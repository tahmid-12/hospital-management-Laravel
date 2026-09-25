CREATE ROLE hospital_management WITH LOGIN PASSWORD '123456';
SELECT 'CREATE DATABASE hospital_management OWNER hospital_management'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'hospital_management')\gexec
GRANT ALL PRIVILEGES ON DATABASE hospital_management TO hospital_management;
