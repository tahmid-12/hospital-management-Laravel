# End-to-end walkthrough of the Hospital Management API.
# Start the server first:  php artisan serve
# Then run:                powershell -ExecutionPolicy Bypass -File docs\api-demo.ps1

$base = 'http://127.0.0.1:8000/api/v1'
$run  = Get-Random -Maximum 99999   # keeps emails / license numbers unique between runs

function Call($method, $path, $body = $null, $token = $null) {
    $headers = @{ Accept = 'application/json' }
    if ($token) { $headers.Authorization = "Bearer $token" }
    $params = @{ Method = $method; Uri = "$base$path"; Headers = $headers; UseBasicParsing = $true }
    if ($body) { $params.Body = ($body | ConvertTo-Json); $params.ContentType = 'application/json' }

    Write-Host "`n>>> $method /api/v1$path" -ForegroundColor Cyan
    try {
        $res = Invoke-WebRequest @params
        Write-Host "HTTP $($res.StatusCode)" -ForegroundColor Green
        $json = $res.Content | ConvertFrom-Json
    } catch {
        $code = [int]$_.Exception.Response.StatusCode
        Write-Host "HTTP $code" -ForegroundColor Yellow
        $stream = $_.Exception.Response.GetResponseStream()
        $json = (New-Object IO.StreamReader($stream)).ReadToEnd() | ConvertFrom-Json
    }
    Write-Host ($json | ConvertTo-Json -Depth 6)
    return $json
}

# 1. Auth
$admin = Call POST '/auth/login' @{ email = 'admin@hospital.test'; password = 'password123' }
$token = $admin.data.token
Call GET '/auth/me' -token $token | Out-Null

# 2. Error cases
Call GET '/departments' | Out-Null                                  # no token -> 401
Call POST '/departments' @{ description = 'no name' } $token | Out-Null  # validation -> 422

# 3. Department
$dept = Call POST '/departments' @{ department_name = "Cardiology $run"; description = 'Heart care' } $token
$deptId = $dept.data.department_id
Call POST '/departments' @{ department_name = "Cardiology $run" } $token | Out-Null  # duplicate -> 422

# 4. Doctor (needs a user account with role=doctor)
$docUser = Call POST '/auth/register' @{ username = "drsmith$run"; email = "drsmith$run@hospital.test"; password = 'password123'; password_confirmation = 'password123'; role = 'doctor' }
$doctor = Call POST '/doctors' @{ user_id = $docUser.data.user.user_id; department_id = $deptId; first_name = 'John'; last_name = 'Smith'; specialization = 'Cardiologist'; license_number = "LIC-$run"; phone = '01700000000' } $token
$doctorId = $doctor.data.doctor_id

# 5. Patient (needs a user account with role=patient)
$patUser = Call POST '/auth/register' @{ username = "jane$run"; email = "jane$run@hospital.test"; password = 'password123'; password_confirmation = 'password123'; role = 'patient' }
$patient = Call POST '/patients' @{ user_id = $patUser.data.user.user_id; first_name = 'Jane'; last_name = 'Doe'; date_of_birth = '1990-05-14'; gender = 'female'; blood_group = 'O+'; phone = '01800000000' } $token
$patientId = $patient.data.patient_id

# 6. Appointment linking the two
$appt = Call POST '/appointments' @{ doctor_id = $doctorId; patient_id = $patientId; appointment_date = '2026-10-01'; appointment_time = '10:30'; reason = 'Chest pain check-up' } $token
Call PATCH "/appointments/$($appt.data.appointment_id)" @{ status = 'completed'; notes = 'ECG normal' } $token | Out-Null

# 7. Medicine and room
Call POST '/medicines' @{ name = "Paracetamol $run"; dosage_form = 'Tablet'; strength = '500mg'; stock_quantity = 200 } $token | Out-Null
Call POST '/rooms' @{ room_number = "R-$run"; room_type = 'ICU'; status = 'available' } $token | Out-Null

# 8. Read back
Call GET '/appointments' -token $token | Out-Null
Call GET "/doctors/$doctorId" -token $token | Out-Null
