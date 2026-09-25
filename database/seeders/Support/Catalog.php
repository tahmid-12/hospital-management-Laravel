<?php

namespace Database\Seeders\Support;

/**
 * Static reference data used to build realistic rows.
 * Lists are ordered roughly from most to least common where order matters.
 */
final class Catalog
{
    public const MALE_FIRST_NAMES = [
        'Rahim', 'Karim', 'Abdul', 'Mohammad', 'Hasan', 'Hossain', 'Rafiq', 'Jamal', 'Kamal', 'Nasir',
        'Faruk', 'Habib', 'Imran', 'Sajid', 'Tanvir', 'Arif', 'Shakil', 'Sohel', 'Mizan', 'Rubel',
        'Anis', 'Babul', 'Delwar', 'Enamul', 'Fahim', 'Golam', 'Jahid', 'Khalid', 'Liton', 'Mahbub',
        'Nayeem', 'Omar', 'Parvez', 'Rakib', 'Saiful', 'Tareq', 'Wahid', 'Yasin', 'Zahid', 'Ashraf',
        'Bilal', 'Didar', 'Emon', 'Farhan', 'Hafiz', 'Iqbal', 'Jubayer', 'Kawsar', 'Masud', 'Nahid',
        'Rashed', 'Shahin', 'Tuhin', 'Ujjal', 'Rajesh', 'Subrata', 'Amit', 'Pranab', 'Sanjoy', 'Dipankar',
    ];

    public const FEMALE_FIRST_NAMES = [
        'Fatema', 'Ayesha', 'Nasrin', 'Shirin', 'Rokeya', 'Salma', 'Taslima', 'Jannat', 'Sumaiya', 'Farzana',
        'Nusrat', 'Sharmin', 'Rehana', 'Shahnaz', 'Mahmuda', 'Rozina', 'Sabina', 'Tahmina', 'Laila', 'Moushumi',
        'Afroza', 'Bilkis', 'Dilara', 'Eshita', 'Fahmida', 'Hasina', 'Ishrat', 'Jesmin', 'Kulsum', 'Lubna',
        'Marium', 'Nadia', 'Parveen', 'Rumana', 'Sadia', 'Tania', 'Umme', 'Yasmin', 'Zakia', 'Anika',
        'Bristy', 'Champa', 'Dipti', 'Keya', 'Mim', 'Nabila', 'Oishee', 'Priya', 'Riya', 'Sraboni',
        'Tithi', 'Urmi', 'Mitu', 'Shapla', 'Popy', 'Rina', 'Mousumi', 'Anjana', 'Shampa', 'Puja',
    ];

    public const LAST_NAMES = [
        'Hossain', 'Islam', 'Rahman', 'Ahmed', 'Khan', 'Uddin', 'Akter', 'Begum', 'Chowdhury', 'Alam',
        'Sarker', 'Miah', 'Sheikh', 'Talukder', 'Mollah', 'Bhuiyan', 'Haque', 'Karim', 'Ali', 'Das',
        'Roy', 'Saha', 'Paul', 'Ghosh', 'Dey', 'Biswas', 'Mondal', 'Sikder', 'Majumder', 'Siddique',
        'Kabir', 'Mahmud', 'Hasan', 'Aziz', 'Sultana', 'Parvin', 'Khatun', 'Mia', 'Howlader', 'Bepari',
        'Matubbar', 'Patwary', 'Sardar', 'Mazumder', 'Chakraborty', 'Barua', 'Tripura', 'Chakma', 'Gomes', 'Rozario',
    ];

    /** Areas (and their share of patients). Dhaka dominates, as it would for a Dhaka hospital. */
    public const AREAS = [
        'Mirpur, Dhaka' => 12, 'Mohammadpur, Dhaka' => 9, 'Dhanmondi, Dhaka' => 7, 'Uttara, Dhaka' => 8,
        'Badda, Dhaka' => 6, 'Jatrabari, Dhaka' => 6, 'Gulshan, Dhaka' => 3, 'Banani, Dhaka' => 2,
        'Motijheel, Dhaka' => 3, 'Rampura, Dhaka' => 5, 'Tejgaon, Dhaka' => 3, 'Keraniganj, Dhaka' => 4,
        'Savar, Dhaka' => 5, 'Gazipur' => 7, 'Narayanganj' => 6, 'Tongi, Gazipur' => 3,
        'Chattogram' => 3, 'Cumilla' => 2, 'Mymensingh' => 2, 'Sylhet' => 1, 'Rajshahi' => 1, 'Khulna' => 1,
    ];

    /** Mobile operator prefixes and their market share. */
    public const PHONE_PREFIXES = ['017' => 30, '013' => 10, '019' => 20, '014' => 5, '018' => 18, '016' => 12, '015' => 5];

    public const BLOOD_GROUPS = ['O+' => 34, 'B+' => 30, 'A+' => 24, 'AB+' => 7, 'O-' => 2, 'B-' => 1.5, 'A-' => 1, 'AB-' => 0.5];

    /** [lower age, upper age] => weight */
    public const AGE_BANDS = [
        '0-4' => 8, '5-17' => 14, '18-29' => 20, '30-44' => 24, '45-59' => 19, '60-74' => 11, '75-92' => 4,
    ];

    public const RELATIONS = ['Husband', 'Wife', 'Father', 'Mother', 'Son', 'Daughter', 'Brother', 'Sister'];

    /** name => [description, weight (share of doctors and patients), specializations] */
    public const DEPARTMENTS = [
        'General Medicine' => ['Diagnosis and non-surgical treatment of adult diseases.', 14, ['Internal Medicine Specialist', 'General Physician']],
        'Pediatrics' => ['Medical care for infants, children and adolescents.', 10, ['Pediatrician', 'Pediatric Pulmonologist']],
        'Obstetrics & Gynecology' => ['Pregnancy, childbirth and women\'s reproductive health.', 9, ['Gynecologist', 'Obstetrician']],
        'Cardiology' => ['Heart and blood vessel disorders.', 8, ['Cardiologist', 'Interventional Cardiologist']],
        'Emergency Medicine' => ['Acute illness and injury care, 24/7.', 6, ['Emergency Physician']],
        'Orthopedics' => ['Bones, joints, ligaments and spine.', 7, ['Orthopedic Surgeon', 'Spine Surgeon']],
        'General Surgery' => ['Abdominal and general surgical procedures.', 7, ['General Surgeon', 'Laparoscopic Surgeon']],
        'Endocrinology' => ['Diabetes, thyroid and hormonal disorders.', 4, ['Endocrinologist', 'Diabetologist']],
        'Dermatology' => ['Skin, hair and nail conditions.', 5, ['Dermatologist']],
        'ENT' => ['Ear, nose and throat.', 5, ['ENT Specialist', 'Head & Neck Surgeon']],
        'Ophthalmology' => ['Eye diseases and vision care.', 5, ['Ophthalmologist']],
        'Gastroenterology' => ['Digestive system and liver.', 4, ['Gastroenterologist', 'Hepatologist']],
        'Pulmonology' => ['Lungs and respiratory diseases.', 4, ['Pulmonologist', 'Chest Specialist']],
        'Neurology' => ['Brain, spinal cord and nerves.', 4, ['Neurologist']],
        'Dentistry' => ['Oral and dental health.', 4, ['Dental Surgeon', 'Orthodontist']],
        'Nephrology' => ['Kidney diseases and dialysis.', 3, ['Nephrologist']],
        'Urology' => ['Urinary tract and male reproductive organs.', 3, ['Urologist']],
        'Psychiatry' => ['Mental health and behavioural disorders.', 3, ['Psychiatrist']],
        'Oncology' => ['Cancer diagnosis and treatment.', 3, ['Medical Oncologist', 'Clinical Oncologist']],
        'Physical Medicine & Rehabilitation' => ['Physiotherapy and rehabilitation.', 2, ['Physiatrist']],
    ];

    public const APPOINTMENT_REASONS = [
        'Fever and cough', 'Follow-up visit', 'Routine check-up', 'Chest pain', 'Headache', 'Abdominal pain',
        'High blood pressure review', 'Diabetes follow-up', 'Back pain', 'Joint pain', 'Skin rash',
        'Shortness of breath', 'Pregnancy check-up', 'Child vaccination consultation', 'Sore throat',
        'Dizziness', 'Eye irritation', 'Ear pain', 'Toothache', 'Report review', 'Weight loss',
        'Anxiety and sleep problems', 'Urinary discomfort', 'Allergy symptoms', 'Post-operative review',
        'Thyroid follow-up', 'Kidney function review', 'Diarrhoea', 'Fatigue', 'Numbness in hands',
    ];

    public const APPOINTMENT_NOTES = [
        'Patient advised lifestyle modification.', 'Investigations advised, review with reports.',
        'Symptoms improving on current treatment.', 'Referred for specialist opinion.',
        'Medication dose adjusted.', 'Review after two weeks.', 'Vitals stable.',
    ];

    /** ICD-10 style diagnoses, most common first (sampled with a Zipf skew). */
    public const DIAGNOSES = [
        'J06.9 Acute upper respiratory infection', 'I10 Essential hypertension', 'E11.9 Type 2 diabetes mellitus',
        'A09 Infectious gastroenteritis', 'K30 Functional dyspepsia', 'J02.9 Acute pharyngitis',
        'M54.5 Low back pain', 'A90 Dengue fever', 'N39.0 Urinary tract infection', 'J45.9 Asthma',
        'D50.9 Iron deficiency anaemia', 'E03.9 Hypothyroidism', 'R51 Headache', 'M17.9 Osteoarthritis of knee',
        'L30.9 Dermatitis', 'K21.9 Gastro-oesophageal reflux disease', 'J18.9 Pneumonia', 'A01.0 Typhoid fever',
        'E78.5 Hyperlipidaemia', 'I25.9 Chronic ischaemic heart disease', 'F41.1 Generalized anxiety disorder',
        'H10.9 Conjunctivitis', 'H66.9 Otitis media', 'K02.9 Dental caries', 'B35.4 Tinea corporis',
        'O26.9 Pregnancy related condition', 'J44.9 Chronic obstructive pulmonary disease', 'N18.9 Chronic kidney disease',
        'G43.9 Migraine', 'M79.1 Myalgia', 'R50.9 Fever of unknown origin', 'K59.0 Constipation',
        'I50.9 Heart failure', 'F32.9 Depressive episode', 'L70.0 Acne vulgaris', 'N20.0 Kidney stone',
        'K80.2 Gallstones', 'G40.9 Epilepsy', 'I63.9 Cerebral infarction', 'C50.9 Breast cancer',
    ];

    public const SYMPTOMS = [
        'fever', 'cough', 'headache', 'body ache', 'fatigue', 'nausea', 'vomiting', 'loose stool', 'abdominal pain',
        'chest tightness', 'shortness of breath', 'dizziness', 'sore throat', 'runny nose', 'joint pain', 'back pain',
        'itching', 'rash', 'burning urination', 'loss of appetite', 'palpitations', 'swelling of legs', 'blurred vision',
        'ear pain', 'insomnia', 'weight loss', 'excessive thirst', 'frequent urination', 'numbness', 'heartburn',
    ];

    public const TREATMENT_NOTES = [
        'Symptomatic treatment given. Plenty of fluids and rest advised.',
        'Started on medication as prescribed. Review in 7 days.',
        'Blood tests advised. Continue current medication.',
        'Diet and exercise counselling given. Follow up in one month.',
        'Referred for imaging. Pain management started.',
        'Chronic condition stable. Continue maintenance therapy.',
        'Antibiotic course started. Return if fever persists beyond 3 days.',
        'Dressing done. Wound care instructions given.',
    ];

    public const PRESCRIPTION_NOTES = [
        'Review after 2 weeks.', 'Continue previous medicines.', 'Avoid oily and spicy food.',
        'Check blood sugar regularly.', 'Complete the full antibiotic course.', 'Return immediately if symptoms worsen.',
    ];

    /** generic => [base price per unit (BDT), forms, strengths] */
    public const GENERICS = [
        'Paracetamol' => [1.2, ['Tablet', 'Syrup', 'Suppository'], ['500 mg', '650 mg', '120 mg/5 ml']],
        'Omeprazole' => [5, ['Capsule'], ['20 mg', '40 mg']],
        'Esomeprazole' => [7, ['Tablet', 'Capsule'], ['20 mg', '40 mg']],
        'Pantoprazole' => [6, ['Tablet'], ['20 mg', '40 mg']],
        'Amoxicillin' => [6, ['Capsule', 'Suspension'], ['250 mg', '500 mg', '125 mg/5 ml']],
        'Azithromycin' => [30, ['Tablet', 'Suspension'], ['250 mg', '500 mg', '200 mg/5 ml']],
        'Cefixime' => [28, ['Capsule', 'Suspension'], ['200 mg', '400 mg', '100 mg/5 ml']],
        'Ciprofloxacin' => [12, ['Tablet'], ['250 mg', '500 mg']],
        'Metformin' => [4, ['Tablet'], ['500 mg', '850 mg', '1000 mg']],
        'Gliclazide' => [8, ['Tablet'], ['30 mg', '80 mg']],
        'Insulin Glargine' => [900, ['Injection'], ['100 IU/ml']],
        'Amlodipine' => [5, ['Tablet'], ['5 mg', '10 mg']],
        'Losartan' => [8, ['Tablet'], ['25 mg', '50 mg']],
        'Bisoprolol' => [7, ['Tablet'], ['2.5 mg', '5 mg']],
        'Atorvastatin' => [10, ['Tablet'], ['10 mg', '20 mg', '40 mg']],
        'Rosuvastatin' => [14, ['Tablet'], ['5 mg', '10 mg', '20 mg']],
        'Clopidogrel' => [12, ['Tablet'], ['75 mg']],
        'Aspirin' => [1, ['Tablet'], ['75 mg', '300 mg']],
        'Levothyroxine' => [2, ['Tablet'], ['25 mcg', '50 mcg', '100 mcg']],
        'Montelukast' => [16, ['Tablet'], ['4 mg', '5 mg', '10 mg']],
        'Salbutamol' => [300, ['Inhaler', 'Syrup'], ['100 mcg', '2 mg/5 ml']],
        'Fexofenadine' => [8, ['Tablet'], ['120 mg', '180 mg']],
        'Cetirizine' => [2.5, ['Tablet', 'Syrup'], ['10 mg', '5 mg/5 ml']],
        'Loratadine' => [3, ['Tablet'], ['10 mg']],
        'Domperidone' => [3, ['Tablet', 'Suspension'], ['10 mg', '5 mg/5 ml']],
        'Ondansetron' => [9, ['Tablet', 'Injection'], ['4 mg', '8 mg']],
        'Metronidazole' => [2.5, ['Tablet', 'Suspension'], ['400 mg', '200 mg/5 ml']],
        'Oral Rehydration Salts' => [6, ['Sachet'], ['10.25 g']],
        'Zinc Sulfate' => [2, ['Tablet', 'Syrup'], ['20 mg', '10 mg/5 ml']],
        'Calcium Carbonate + Vitamin D3' => [9, ['Tablet'], ['500 mg + 200 IU', '600 mg + 400 IU']],
        'Iron + Folic Acid' => [3, ['Capsule', 'Tablet'], ['150 mg + 0.5 mg']],
        'Vitamin B Complex' => [2, ['Tablet', 'Syrup'], ['Standard']],
        'Diclofenac' => [3, ['Tablet', 'Gel', 'Injection'], ['50 mg', '75 mg', '1%']],
        'Naproxen' => [7, ['Tablet'], ['250 mg', '500 mg']],
        'Tramadol' => [6, ['Capsule', 'Injection'], ['50 mg', '100 mg']],
        'Prednisolone' => [1.5, ['Tablet'], ['5 mg', '20 mg']],
        'Hydrocortisone' => [45, ['Cream', 'Injection'], ['1%', '100 mg']],
        'Clotrimazole' => [60, ['Cream'], ['1%']],
        'Fluconazole' => [18, ['Capsule'], ['50 mg', '150 mg']],
        'Terbinafine' => [22, ['Tablet', 'Cream'], ['250 mg', '1%']],
        'Mupirocin' => [120, ['Ointment'], ['2%']],
        'Chloramphenicol' => [35, ['Eye Drops'], ['0.5%']],
        'Moxifloxacin' => [110, ['Eye Drops', 'Tablet'], ['0.5%', '400 mg']],
        'Timolol' => [150, ['Eye Drops'], ['0.5%']],
        'Sertraline' => [8, ['Tablet'], ['50 mg', '100 mg']],
        'Escitalopram' => [9, ['Tablet'], ['5 mg', '10 mg']],
        'Clonazepam' => [5, ['Tablet'], ['0.5 mg', '2 mg']],
        'Gabapentin' => [12, ['Capsule'], ['100 mg', '300 mg']],
        'Pregabalin' => [15, ['Capsule'], ['25 mg', '75 mg']],
        'Levetiracetam' => [20, ['Tablet'], ['250 mg', '500 mg']],
        'Furosemide' => [2, ['Tablet', 'Injection'], ['20 mg', '40 mg']],
        'Spironolactone' => [6, ['Tablet'], ['25 mg', '50 mg']],
        'Tamsulosin' => [14, ['Capsule'], ['0.4 mg']],
        'Ceftriaxone' => [180, ['Injection'], ['500 mg', '1 g', '2 g']],
        'Meropenem' => [950, ['Injection'], ['500 mg', '1 g']],
        'Enoxaparin' => [650, ['Injection'], ['40 mg', '60 mg']],
        'Lactulose' => [160, ['Syrup'], ['3.35 g/5 ml']],
        'Ursodeoxycholic Acid' => [20, ['Tablet'], ['150 mg', '300 mg']],
        'Tamoxifen' => [25, ['Tablet'], ['10 mg', '20 mg']],
        'Folic Acid' => [1, ['Tablet'], ['5 mg']],
    ];

    /** Syllables combined into brand names ("Napa", "Seclo", ...). */
    public const BRAND_START = ['Na', 'Se', 'Ma', 'Ro', 'Zi', 'Ce', 'Lo', 'Fe', 'Mo', 'Ri', 'Ta', 'Vi', 'Xe', 'Do', 'Pa', 'Ka', 'Li', 'Bi', 'Fi', 'Gl', 'Tr', 'Cl', 'Pr', 'Ne', 'Or', 'Al', 'Am', 'Ev', 'On', 'Ul'];

    public const BRAND_MIDDLE = ['', 'pa', 'clo', 'mox', 'zo', 'ra', 'li', 've', 'to', 'fe', 'no', 'bi', 'lu', 'cor', 'mi', 'xi', 'de', 'ta', 'ri', 'sa'];

    public const BRAND_END = ['n', 'x', 'l', 'm', 'ne', 'on', 'ex', 'il', 'um', 'ac', 'in', 'ol', 'id', 'et', 'or', 'ix', 'ate', 'ine', 'ase', 'ra', 'lin', 'cid', 'pro', 'max', 'pin'];

    /** form => [dosage text, frequency => doses per day] */
    public const FORM_DOSAGE = [
        'Tablet' => '1 tablet', 'Capsule' => '1 capsule', 'Syrup' => '10 ml', 'Suspension' => '5 ml',
        'Suppository' => '1 suppository', 'Injection' => '1 vial', 'Inhaler' => '2 puffs', 'Sachet' => '1 sachet',
        'Cream' => 'Apply thin layer', 'Gel' => 'Apply thin layer', 'Ointment' => 'Apply thin layer', 'Eye Drops' => '1 drop',
    ];

    /** Forms bought as one pack per prescription rather than per dose. */
    public const PACK_FORMS = ['Syrup', 'Suspension', 'Inhaler', 'Cream', 'Gel', 'Ointment', 'Eye Drops'];

    public const FREQUENCIES = ['1+0+1' => 2, '1+1+1' => 3, '0+0+1' => 1, '1+0+0' => 1, 'Once daily' => 1, 'Twice daily' => 2, 'Every 8 hours' => 3, 'As needed' => 1];

    public const FREQUENCY_WEIGHTS = ['1+0+1' => 30, '1+1+1' => 15, '0+0+1' => 12, '1+0+0' => 12, 'Once daily' => 10, 'Twice daily' => 8, 'Every 8 hours' => 5, 'As needed' => 8];

    /** duration => days (used to derive dispensed quantity) */
    public const DURATIONS = ['3 days' => 3, '5 days' => 5, '7 days' => 7, '10 days' => 10, '14 days' => 14, '30 days' => 30, '3 months' => 90, 'Continue' => 30];

    public const DURATION_WEIGHTS = ['3 days' => 10, '5 days' => 20, '7 days' => 25, '10 days' => 10, '14 days' => 10, '30 days' => 12, '3 months' => 5, 'Continue' => 8];

    public const INSTRUCTIONS = ['After meal', 'Before meal', 'At bedtime', 'With plenty of water', 'Empty stomach in the morning', null, null];

    /** type => [share of rooms, daily rate (BDT), median length of stay (days), nursing per day (BDT)] */
    public const ROOM_TYPES = [
        'General Ward' => [50, 1200, 3, 500],
        'Semi-Private' => [15, 3000, 3, 800],
        'Private Cabin' => [12, 6000, 3, 1000],
        'Deluxe Cabin' => [4, 9500, 3, 1200],
        'ICU' => [7, 18000, 4, 4000],
        'CCU' => [3, 15000, 4, 3500],
        'HDU' => [4, 9000, 3, 2500],
        'NICU' => [5, 12000, 6, 3000],
    ];

    public const ADMISSION_REASONS = [
        'Observation after chest pain', 'Dengue fever with low platelets', 'Post-operative care', 'Normal delivery',
        'Caesarean section', 'Severe dehydration', 'Pneumonia', 'Uncontrolled diabetes', 'Road traffic accident',
        'Acute appendicitis', 'Stroke management', 'Asthma exacerbation', 'Kidney stone surgery', 'Fracture fixation',
        'Chemotherapy cycle', 'Neonatal jaundice', 'Heart failure management', 'Typhoid fever',
    ];

    /** name => [price (BDT), weight] */
    public const LAB_TESTS = [
        'Complete Blood Count (CBC)' => [400, 30], 'Random Blood Sugar' => [150, 18], 'HbA1c' => [1000, 10],
        'Lipid Profile' => [1200, 10], 'Serum Creatinine' => [400, 12], 'SGPT (ALT)' => [400, 10],
        'Urine Routine Examination' => [300, 14], 'Chest X-Ray' => [700, 9], 'ECG' => [500, 12],
        'Ultrasonography of Whole Abdomen' => [1800, 7], 'TSH' => [800, 6], 'Dengue NS1 Antigen' => [500, 6],
        'Echocardiography' => [3000, 3], 'CT Scan of Brain' => [5500, 1.5], 'MRI of Lumbar Spine' => [9000, 1],
        'Widal Test' => [350, 4], 'Serum Electrolytes' => [900, 5], 'Blood Culture' => [1200, 2],
    ];

    /** Extra procedures on a consultation bill: name => price (BDT) */
    public const PROCEDURES = [
        'Dressing' => 300, 'Nebulization' => 250, 'Injection administration' => 150, 'ECG' => 500,
        'Suture removal' => 400, 'Blood pressure monitoring' => 100, 'Ear wax removal' => 600, 'IV cannulation' => 350,
    ];

    /** Extra charges on an admission bill: name => [min, max] (BDT) */
    public const ADMISSION_EXTRAS = [
        'Operation theatre charge' => [25000, 90000],
        'Medicines and consumables' => [2000, 45000],
        'Laboratory investigations' => [1500, 18000],
        'Oxygen supply' => [1500, 12000],
        'Blood transfusion' => [3500, 7000],
        'Physiotherapy sessions' => [800, 8000],
        'Anaesthesia charge' => [8000, 25000],
    ];
}
