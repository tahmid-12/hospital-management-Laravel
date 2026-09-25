# Database Seeding

This project's seeders build a realistic, **large** hospital dataset for learning PostgreSQL:
indexing, `EXPLAIN ANALYZE`, joins, aggregation, pagination, locking, WAL, replication and more.

The seeders only **add** rows. They do not change the schema, add indexes, or delete data.

---

## Quick start

```powershell
cd hospital-management-api

# Small dataset (default) - about 10k rows, about 1 second
php artisan db:seed

# Performance dataset - about 2.3 million rows, about 3 minutes
$env:SEED_PROFILE = 'performance'; php artisan db:seed; Remove-Item Env:SEED_PROFILE
```

Bash equivalent: `SEED_PROFILE=performance php artisan db:seed`

A second run on the same database is refused (see [Rebuilding the data](#rebuilding-the-data-safely)).

---

## Settings

All settings live in [config/seeding.php](../config/seeding.php) and can be overridden with environment variables:

| Variable | Default | Meaning |
|---|---|---|
| `SEED_PROFILE` | `small` | `small` or `performance` |
| `SEED_RANDOM_SEED` | `20260925` | Same seed gives the same data, down to the UUIDs |
| `SEED_REFERENCE_DATE` | `2026-09-25` | The "today" of the dataset: past vs. future appointments, open bills, current admissions |
| `SEED_CHUNK_SIZE` | `1000` | Rows per `INSERT` statement |

The config file also holds every distribution: year weights, patient activity classes, status mixes,
payment methods and the doctor/medicine skew. Change them there, not in the seeders.

---

## What gets generated

The profile only decides how many **parent** rows exist. Child tables grow out of the relationships,
so their counts are *emergent*, not fixed targets.

| Table | Seeder | small | performance | How the count arises |
|---|---|---:|---:|---|
| departments | DepartmentSeeder | 8 | 18 | Profile; existing departments with the same name are reused |
| users | UserSeeder | 106 | 20,290 | Admins + staff + one per doctor + 95% of patients |
| doctors | DoctorSeeder | 10 | 85 | Profile |
| patients | PatientSeeder | 100 | 20,000 | Profile; 5% are walk-ins with `user_id = NULL` |
| medicines | MedicineSeeder | 100 | 15,000 | Profile |
| rooms | RoomSeeder | 20 | 400 | Profile |
| appointments | AppointmentSeeder | ~550 | 135,662 | Each patient's activity class (0 to 80 bookings) |
| medical_records | MedicalRecordSeeder | ~360 | 88,061 | 93% of completed visits + walk-in encounters |
| prescriptions | PrescriptionSeeder | ~600 | 166,184 | 0-3 per completed visit + monthly refills for frequent/chronic patients |
| prescription_items | PrescriptionItemSeeder | ~1,600 | 474,536 | 1-8 medicines per prescription (mostly 2-3) |
| admissions | AdmissionSeeder | ~260 | 50,843 | 0-30 stays per patient, driven by activity class |
| bills | BillSeeder | ~1,100 | 275,247 | Consultations + lab orders + admissions + pharmacy purchases |
| bill_items | BillItemSeeder | ~3,500 | 838,833 | Fee lines, lab tests, room nights, dispensed medicines |
| payments | PaymentSeeder | ~1,450 | 351,767 | Installments that match each bill's status |

Performance numbers are for the default seed. A different seed gives slightly different totals.

### What each seeder does

- **DepartmentSeeder**: real specialties (General Medicine, Pediatrics, Cardiology, ...) with a size weight.
- **UserSeeder**: accounts for admins, staff, doctors and patients. Usernames look like `rahim.hossain123`. Every seeded password is `password`.
- **DoctorSeeder**: each department gets at least one doctor; larger departments get more. Unique `BMDC-A-xxxxx` license numbers.
- **PatientSeeder**: Bangladeshi names, `01XXXXXXXXX` mobile numbers (about 4% share a family number, about 3% have none), age bands, blood groups, Dhaka-heavy addresses. Also assigns each patient an **activity class** and a **usual doctor**.
- **MedicineSeeder**: brand names built on about 60 real generics (`Seclomox 500 mg` is Amoxicillin), with forms, strengths, prices and stock levels (about 6% out of stock).
- **RoomSeeder**: rooms `A-101` to `C-220` of 8 types (General Ward ... ICU, NICU), each with a daily rate.
- **AppointmentSeeder**: bookings in morning and evening sessions, mostly with the patient's usual doctor. Status depends on whether the date is past or future.
- **MedicalRecordSeeder**: diagnosis (ICD-10 style, skewed toward common ones), symptoms and notes, written 10-60 minutes after the visit.
- **PrescriptionSeeder**: prescriptions written during the visit, plus 28-35-day refills (`appointment_id = NULL`) for frequent and chronic patients.
- **PrescriptionItemSeeder**: distinct medicines per prescription, with dosage, frequency (`1+0+1`), duration and instructions. Refills repeat the original medicines.
- **AdmissionSeeder**: non-overlapping stays per patient, with a log-normal length of stay by room type. Stays still running on the reference date stay `admitted`, and their rooms are set to `occupied`.
- **BillSeeder**: one bill per billable event, inserted in **date order**.
- **BillItemSeeder**: line items per bill type, then one set-based `UPDATE` sets `bills.total_amount = SUM(bill_items.amount)`.
- **PaymentSeeder**: `paid` bills get installments that add up exactly to the total; `partially_paid` bills get 20-80%; `pending` and `cancelled` bills get none.

---

## How it works

```
DatabaseSeeder
  ├─ guard: refuse if seed data already exists
  ├─ BEGIN
  │    Department → User → Doctor → Patient → Medicine → Room
  │    → Appointment → MedicalRecord → Prescription → PrescriptionItem
  │    → Admission → Bill → BillItem → Payment
  ├─ COMMIT
  └─ VACUUM ANALYZE each table
```

- **Shared context** ([database/seeders/Support/SeedContext.php](../database/seeders/Support/SeedContext.php)): each seeder records the IDs it generates, and later seeders take their foreign keys from there. A foreign key can only point at a row inserted earlier in the same run, so there are no random or dangling IDs. The foreign-key constraints in the schema check this as well. The context stores plain arrays of strings and ints, not Eloquent models, so the full run peaks at about 230 MB of PHP memory.
- **One transaction**: the whole run commits once. If anything fails, PostgreSQL rolls everything back and the database looks untouched.
- **VACUUM ANALYZE afterwards**: `ANALYZE` refreshes the planner statistics, so `EXPLAIN` shows realistic plans straight away. `VACUUM` cleans up the dead row versions left by the bill-total `UPDATE`.
- Because the seeders share one context, run them through `DatabaseSeeder`. `db:seed --class=AppointmentSeeder` on its own fails.

---

## Why the dataset is built this way

### Why so large
With a few hundred rows, every table fits in one or two 8 KB pages. PostgreSQL then reads the whole table
(a sequential scan) because that is cheapest, and an index never looks useful. With hundreds of thousands of rows, a bad plan becomes
visibly slow. That makes the difference between sequential scans, index scans, bitmap scans, hash joins and
nested loops, and in-memory versus on-disk sorts, measurable.

### Why realistic distributions
The planner chooses plans from **statistics**: most-common values, histograms and the number of distinct values
(`pg_stats`). Uniform random data hides the effects you want to learn. With real-looking distributions:
- `status = 'completed'` (60% of rows) should be a sequential scan, while `status = 'missed'` (5%) can use the index. Same query, different plan.
- `bills.status` has 4 values with very different frequencies, which is a good case for partial indexes.

Distributions used (from `config/seeding.php`):

| Field | Mix |
|---|---|
| appointments.status | completed 60%, scheduled 24%, cancelled 11%, missed 5% |
| bills.status | paid 65%, pending 15%, cancelled 10%, partially_paid 10% |
| payments.payment_method | cash 45%, bKash 25%, card 18%, Nagad 7%, bank transfer 3%, insurance 2% |
| patients | 95% have an account; blood group missing for 15%; phone missing for 3% |

Appointment statuses use the API's vocabulary (`scheduled/completed/cancelled/missed`), so the API can still edit every row.
`scheduled` covers both "scheduled" and "confirmed". Future appointments are `scheduled` or `cancelled`.
Some **past** appointments are still `scheduled` because nobody updated them. That is deliberate; stale rows like this are a common data-quality query.

### Why historical dates
Clinical data spans 2022-2026 (target weights 10/15/20/25/30%). This lets you practise date-range filters,
`date_trunc` reports, and later **partitioning by year or month**, where you need several years of data.

The result leans a little more recent than the weights: appointments come out 7/13/20/27/34%.
A patient can't have appointments before they register, and later registrants can only add to later years.

Dates are consistent with each other:
- Payment ≥ bill date; discharge > admission; medical record ≥ visit time.
- A prescription is on its appointment's date; refills come 28-35 days apart.
- No appointment is dated before the patient registered, and nothing except future appointments is after the reference date.
- Setup data (departments, rooms, medicines, doctors) was created in 2019, before any clinical activity.

### Why skew
Real workloads are uneven, and uneven data is where index choices matter:
- **Doctors** follow a Zipf curve (`doctor_popularity_skew`): a few doctors have thousands of appointments, while others have a few hundred.
- **Patients** belong to activity classes: 10% never book, 45% book 1-3 times, 30% book 4-11 times, 12% book 11-28 times, and 3% are chronic with 26-80 bookings. Frequent and chronic patients also get monthly refills and many more admissions.
- **Medicines** follow a Zipf curve (`medicine_popularity_skew`): a few brands appear in thousands of prescription items, and many are never prescribed (useful for `NOT EXISTS` / anti-join practice).
- **Patients have many bills**: consultation + lab + pharmacy + admission bills.

Result: `WHERE doctor_id = <busy doctor>` and `WHERE doctor_id = <quiet doctor>` can get *different plans*
from the same index. That is the point.

### Why no experimental indexes were added
The seeders add **no indexes**, so you can create them yourself and compare `EXPLAIN ANALYZE` before and after.
These indexes already exist (from the migrations):

| Table | Existing indexes |
|---|---|
| users | PK, `username` unique, `email` unique |
| departments | PK, `department_name` unique |
| doctors | PK, `license_number` unique, `department_id`, `user_id` |
| patients | PK, `user_id` |
| medicines | PK, `name` unique, `stock_quantity` |
| rooms | PK, `room_number` unique, `status` |
| appointments | PK, `(doctor_id, appointment_date)`, `(patient_id, appointment_date)`, `status` |
| medical_records | PK, `patient_id`, `doctor_id` |
| prescriptions | PK, `patient_id`, `doctor_id` |
| prescription_items | PK, `prescription_id`, `medicine_id` |
| admissions | PK, `patient_id`, `room_id`, `status` |
| bills | PK, `patient_id`, `status` |
| bill_items | PK, `bill_id` |
| payments | PK, `transaction_reference` unique, `bill_id` |

Deliberately **not** indexed, so good first experiments: `patients.phone`, patient names, `appointments.appointment_date` on its own,
`prescriptions.prescribed_at`, `bills.bill_date`, `payments.paid_at`, `admissions.admitted_at`,
and the `appointment_id` foreign keys on `medical_records` and `prescriptions`.

---

## Bulk inserts and chunking

### Why not `Model::create()` per row
Each single-row `INSERT` pays a fixed cost: a network round trip, statement parsing and planning, a separate index update,
and (in autocommit mode) its own commit, which flushes WAL to disk. For 2.3 million rows, that fixed cost dominates. Eloquent adds
model hydration, events, and memory for every object.

Measured on this machine, inserting the same 5,000 rows:

| Method | Time |
|---|---:|
| 5,000 single INSERTs, autocommit | ~1,020 ms |
| 5,000 single INSERTs, one transaction | ~950 ms |
| 5 multi-row INSERTs of 1,000 rows, one transaction | **~54 ms** (about 19× faster) |

The seeders send multi-row statements:

```sql
INSERT INTO appointments (appointment_id, doctor_id, ...) VALUES
  ('019...', '019...', ...),
  ('019...', '019...', ...),
  ... -- 1,000 rows
```

Wrapping single inserts in a transaction removes the per-row commit. On this SSD that saves little, but on slower disks, or with
`synchronous_commit = on` and replication, it matters much more. Batching removes the per-row round trip, which is the big win here.
The next step up is PostgreSQL's `COPY`, which is faster still. Comparing them is a good WAL experiment.

### How chunking works
[BulkInserter](../database/seeders/Support/BulkInserter.php) buffers rows as plain PHP arrays and runs one
`INSERT` for every `SEED_CHUNK_SIZE` rows (default 1,000), then empties the buffer:
- **Memory stays flat.** The generated rows are never all in memory at once.
- **Parameter limit.** PostgreSQL allows 65,535 bind parameters per statement, so the chunk size is capped at `65000 / columns`.
- **Trade-off.** Larger chunks mean fewer round trips, but each statement gets bigger. Try `SEED_CHUNK_SIZE=100` against `5000` and time the run.

Other speed choices: the bcrypt password hash is computed **once** and reused (bcrypt takes about 50-100 ms per call on purpose),
and no queries run inside the generation loops.

---

## UUIDs and reproducibility

- Every primary key is a **UUIDv7**, the same format the app's `HasUuids` produces, so the UUID strategy is unchanged.
  UUIDv7 starts with a timestamp. The seeders embed each record's **own historical time** in its ID, so an ID from a
  2022 row sorts before an ID from a 2026 row, just as it would have if the app had created them live.
- All randomness comes from `Random\Randomizer` with a seeded Mersenne Twister, including the random bits of the UUIDs.
  The same `SEED_RANDOM_SEED` and profile therefore give **identical data, IDs included**. This was verified by comparing checksums of two runs.
- Each seeder has its **own random stream** (base seed + seeder name). Changing how prescriptions are generated
  does not reshuffle patients or appointments.
- To get a different but statistically similar dataset, change the seed: `$env:SEED_RANDOM_SEED = '42'`.
- Only the password hash differs between runs, because bcrypt salts are random.

---

## Running it

### Small dataset (API development)
```powershell
php artisan db:seed
```

### Performance dataset (database experiments)
```powershell
$env:SEED_PROFILE = 'performance'
php artisan db:seed
Remove-Item Env:SEED_PROFILE
```

The run takes about 3 minutes and adds about 500 MB of table and index data. Remove the variable afterwards, or the next `migrate:fresh --seed` will build the large dataset again.

### Recommended: a separate database for experiments
Keep your development database small and put the big dataset in its own database. As `postgres`:

```sql
CREATE DATABASE hospital_perf OWNER hospital_management;
```

Then run the migrations and seeders against it without editing `.env`:

```powershell
$env:DB_DATABASE = 'hospital_perf'; $env:SEED_PROFILE = 'performance'
php artisan migrate
php artisan db:seed
Remove-Item Env:DB_DATABASE, Env:SEED_PROFILE
```

Real environment variables take priority over `.env`, so this doesn't change your normal setup.

### Rebuilding the data safely
The seeders refuse to run twice on the same database: seeded usernames and emails are unique, so a second run
would fail partway through. To rebuild, **in development only**:

```powershell
php artisan migrate:fresh --seed   # DROPS ALL TABLES AND DATA, then migrates and seeds
```

This also deletes anything you created through the API, such as the `admin@hospital.test` account. That is another reason to use a separate database for experiments.

---

## Verifying the data

### Row counts
```sql
SELECT relname AS table_name, n_live_tup AS approx_rows
FROM pg_stat_user_tables
ORDER BY n_live_tup DESC;              -- fast estimate from statistics

SELECT 'appointments' AS t, count(*) FROM appointments
UNION ALL SELECT 'prescription_items', count(*) FROM prescription_items
UNION ALL SELECT 'bills', count(*) FROM bills
UNION ALL SELECT 'bill_items', count(*) FROM bill_items
UNION ALL SELECT 'payments', count(*) FROM payments;   -- exact
```

### Table sizes
```sql
SELECT relname, pg_size_pretty(pg_total_relation_size(relid)) AS total, pg_size_pretty(pg_indexes_size(relid)) AS indexes
FROM pg_statio_user_tables ORDER BY pg_total_relation_size(relid) DESC;
```

### Distributions and skew
```sql
SELECT status, count(*), round(100.0 * count(*) / sum(count(*)) OVER (), 1) AS pct
FROM appointments GROUP BY status ORDER BY 2 DESC;

SELECT extract(year FROM appointment_date) AS year, count(*) FROM appointments GROUP BY 1 ORDER BY 1;

SELECT doctor_id, count(*) FROM appointments GROUP BY 1 ORDER BY 2 DESC LIMIT 5;   -- busiest doctors
SELECT doctor_id, count(*) FROM appointments GROUP BY 1 ORDER BY 2 ASC  LIMIT 5;   -- quietest doctors

SELECT count(*) FILTER (WHERE n = 0) AS never_prescribed, max(n) AS most_prescribed
FROM (SELECT m.medicine_id, count(pi.medicine_id) AS n
      FROM medicines m LEFT JOIN prescription_items pi USING (medicine_id) GROUP BY 1) s;
```

### Consistency (every query should return 0)
```sql
SELECT count(*) FROM payments p JOIN bills b USING (bill_id) WHERE p.paid_at < b.created_at;
SELECT count(*) FROM admissions WHERE discharged_at <= admitted_at;
SELECT count(*) FROM bills b
WHERE b.status = 'paid'
  AND b.total_amount <> (SELECT coalesce(sum(amount), 0) FROM payments p WHERE p.bill_id = b.bill_id);
SELECT count(*) FROM bills b
WHERE b.total_amount <> (SELECT sum(amount) FROM bill_items i WHERE i.bill_id = b.bill_id);
```

---

## Starter EXPLAIN ANALYZE queries

The literal values below come from the **default seed** (`20260925`, performance profile), so they exist in your data too.
With another seed, look up values with the helper queries in [Verifying the data](#verifying-the-data).
"Plan today" is the plan measured with only the existing indexes.

**1. Point lookup on an unindexed column.** Plan today: Seq Scan that discards 19,997 rows to return 3 (a shared family number).
```sql
EXPLAIN ANALYZE SELECT * FROM patients WHERE phone = '01360213362';
```

**2. Selectivity: same index, different plan.** `missed` (5%) uses a Bitmap Index Scan on `status`; `completed` (60%) ignores the index and seq-scans.
```sql
EXPLAIN ANALYZE SELECT * FROM appointments WHERE status = 'missed';
EXPLAIN ANALYZE SELECT * FROM appointments WHERE status = 'completed';
```

**3. Date range with no usable index.** Plan today: Seq Scan over 135k rows for about 3.8k matches. The `(doctor_id, appointment_date)` index can't help because date isn't its leading column.
```sql
EXPLAIN ANALYZE SELECT * FROM appointments WHERE appointment_date BETWEEN '2026-01-01' AND '2026-01-31';
```

**4. Skew: busy doctor vs. quiet doctor.** Both use the composite index, but the busy doctor returns 40× more rows. Try a covering index with `INCLUDE (appointment_time, status)`.
```sql
EXPLAIN ANALYZE SELECT appointment_date, appointment_time, status FROM appointments
WHERE doctor_id = '016d262d-e14a-7c1c-8340-a3a971e9a796' AND appointment_date >= '2025-01-01' ORDER BY appointment_date;  -- busiest

EXPLAIN ANALYZE SELECT appointment_date, appointment_time, status FROM appointments
WHERE doctor_id = '0169e884-b17f-7f0b-a5e5-3355a2246ec7' AND appointment_date >= '2025-01-01' ORDER BY appointment_date;  -- quietest
```

**5. Join + GROUP BY: appointments per department in 2025.** Plan today: nested loops ending in an Index Only Scan. Check `Heap Fetches`.
```sql
EXPLAIN ANALYZE SELECT d.department_name, count(*) AS appointments
FROM appointments a
JOIN doctors doc ON doc.doctor_id = a.doctor_id
JOIN departments d ON d.department_id = doc.department_id
WHERE a.appointment_date >= '2025-01-01' AND a.appointment_date < '2026-01-01'
GROUP BY d.department_name ORDER BY appointments DESC;
```

**6. Aggregation over 474k rows: top 10 medicines.** Plan today: parallel workers, Hash Join, Partial/Finalize HashAggregate, top-N heapsort.
```sql
EXPLAIN ANALYZE SELECT m.name, count(*) AS times_prescribed
FROM prescription_items pi JOIN medicines m ON m.medicine_id = pi.medicine_id
GROUP BY m.name ORDER BY times_prescribed DESC LIMIT 10;
```

**7. Monthly revenue report.** Plan today: Seq Scan, then a sort that **spills to disk** (`external merge`). Try `SET work_mem = '32MB';` and an index on `paid_at`.
```sql
EXPLAIN ANALYZE SELECT date_trunc('month', paid_at) AS month, payment_method, sum(amount) AS collected
FROM payments WHERE paid_at >= '2025-01-01'
GROUP BY 1, 2 ORDER BY 1, 2;
```

**8. Outstanding balance per patient (join + subquery aggregate).** Plan today: aggregates all 351k payments even though only open bills matter. Try rewriting it with `LATERAL` or a filtered subquery.
```sql
EXPLAIN ANALYZE SELECT b.patient_id, sum(b.total_amount - coalesce(p.paid, 0)) AS outstanding
FROM bills b
LEFT JOIN (SELECT bill_id, sum(amount) AS paid FROM payments GROUP BY bill_id) p ON p.bill_id = b.bill_id
WHERE b.status IN ('pending', 'partially_paid')
GROUP BY b.patient_id ORDER BY outstanding DESC LIMIT 20;
```

**9. OFFSET vs. keyset pagination.** OFFSET sorts all 275k bills (spilling to disk) to throw away 200,000 of them. Keyset only needs rows after the cursor. Then add `CREATE INDEX ON bills (bill_date DESC, bill_id DESC)` and compare again.
```sql
EXPLAIN ANALYZE SELECT bill_id, bill_date, total_amount, status FROM bills
ORDER BY bill_date DESC, bill_id DESC LIMIT 20 OFFSET 200000;

EXPLAIN ANALYZE SELECT bill_id, bill_date, total_amount, status FROM bills
WHERE (bill_date, bill_id) < ('2023-06-01', 'ffffffff-ffff-ffff-ffff-ffffffffffff')
ORDER BY bill_date DESC, bill_id DESC LIMIT 20;
```

**10. One patient's full prescription history.** Plan today: nested loops over three index scans, about 2 ms for 428 rows. This is what well-indexed foreign keys look like.
```sql
EXPLAIN ANALYZE SELECT p.prescribed_at, m.name, pi.dosage, pi.frequency, pi.duration
FROM prescriptions p
JOIN prescription_items pi ON pi.prescription_id = p.prescription_id
JOIN medicines m ON m.medicine_id = pi.medicine_id
WHERE p.patient_id = '01879072-b6d5-7f05-8d43-a10b484f07cb'
ORDER BY p.prescribed_at DESC;
```

Tip: `EXPLAIN (ANALYZE, BUFFERS)` also shows how many 8 KB pages each step read from cache versus disk.

---

## Things worth knowing for experiments

- **Physical order.** `users`, `patients` and `bills` are inserted in date order, so their date columns correlate strongly
  with the physical order on disk. `appointments` is inserted patient by patient, so it does not. Compare them:
  `SELECT tablename, attname, correlation FROM pg_stats WHERE attname IN ('bill_date', 'appointment_date');`
  This affects index scan cost, BRIN indexes and `CLUSTER`.
- **Unpaginated API.** The API's list endpoints (`GET /api/v1/appointments`, ...) return every row. With the performance
  dataset they will be very slow. That is a good starting point for comparing offset and keyset pagination.
- **The bill-total UPDATE** rewrites about 275k rows, creating dead row versions and a burst of WAL. `VACUUM ANALYZE` runs afterwards.
  Watch `pg_stat_user_tables.n_dead_tup` if you repeat updates like this yourself.
