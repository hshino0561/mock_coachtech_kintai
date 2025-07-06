#!/bin/bash

# Laravel のテストを指定順で実行
echo "Start ordered Laravel tests..."

php artisan test tests/Feature/Auth --filter=UserRegisterTest
php artisan test tests/Feature/Auth --filter=EmailVerificationTest
php artisan test tests/Feature/Auth --filter=LoginTest
php artisan test tests/Feature/Auth --filter=AdminLoginTest
php artisan test tests/Feature/Attendance --filter=CurrentDatetimeDisplayTest
php artisan test tests/Feature/Attendance --filter=WorkStatusDisplayTest
php artisan test tests/Feature/Attendance --filter=AttendanceStartTest
php artisan test tests/Feature/Attendance --filter=BreakFeatureTest
php artisan test tests/Feature/Attendance --filter=LeaveFeatureTest
php artisan test tests/Feature/Attendance --filter=AttendanceListFeatureTest
php artisan test tests/Feature/Attendance --filter=AttendanceDetailFeatureTest
php artisan test tests/Feature/Attendance --filter=AttendanceCorrectionFeatureTest
php artisan test tests/Feature/Attendance --filter=AdminAttendanceListFeatureTest 
php artisan test tests/Feature/Attendance --filter=AdminAttendanceDetailFeatureTest
php artisan test tests/Feature/Attendance --filter=AdminStaffFeatureTest
php artisan test tests/Feature/Attendance --filter=AdminCorrectionRequestFeatureTest

echo "All tests completed."
