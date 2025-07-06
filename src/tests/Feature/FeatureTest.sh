#!/bin/bash

# Laravel のテストを指定順で実行
echo "Start ordered Laravel tests..."

echo "▶︎ 1：UserRegisterTest"
php artisan test tests/Feature/Auth --filter=UserRegisterTest  --stop-on-failure || exit 1
echo "▶︎ 2：EmailVerificationTest"
php artisan test tests/Feature/Auth --filter=EmailVerificationTest  --stop-on-failure || exit 1
echo "▶︎ 3：LoginTest"
php artisan test tests/Feature/Auth --filter=LoginTest  --stop-on-failure || exit 1
echo "▶︎ 4：AdminLoginTest"
php artisan test tests/Feature/Auth --filter=AdminLoginTest  --stop-on-failure || exit 1
echo "▶︎ 5：CurrentDatetimeDisplayTest"
php artisan test tests/Feature/Attendance --filter=CurrentDatetimeDisplayTest  --stop-on-failure || exit 1
echo "▶︎ 6：WorkStatusDisplayTest"
php artisan test tests/Feature/Attendance --filter=WorkStatusDisplayTest  --stop-on-failure || exit 1
echo "▶︎ 7：AttendanceStartTest"
php artisan test tests/Feature/Attendance --filter=AttendanceStartTest  --stop-on-failure || exit 1
echo "▶︎ 8：BreakFeatureTest"
php artisan test tests/Feature/Attendance --filter=BreakFeatureTest  --stop-on-failure || exit 1
echo "▶︎ 9：LeaveFeatureTest"
php artisan test tests/Feature/Attendance --filter=LeaveFeatureTest  --stop-on-failure || exit 1
echo "▶︎ 10：AttendanceListFeatureTest"
php artisan test tests/Feature/Attendance --filter=AttendanceListFeatureTest  --stop-on-failure || exit 1
echo "▶︎ 11：AttendanceDetailFeatureTest"
php artisan test tests/Feature/Attendance/AttendanceDetailFeatureTest.php  --stop-on-failure || exit 1
echo "▶︎ 12：AttendanceCorrectionFeatureTest"
php artisan test tests/Feature/Attendance --filter=AttendanceCorrectionFeatureTest  --stop-on-failure || exit 1
echo "▶︎ 13：AdminAttendanceListFeatureTest"
php artisan test tests/Feature/Attendance --filter=AdminAttendanceListFeatureTest  --stop-on-failure || exit 1
echo "▶︎ 14：AdminAttendanceDetailFeatureTest"
php artisan test tests/Feature/Attendance --filter=AdminAttendanceDetailFeatureTest  --stop-on-failure || exit 1
echo "▶︎ 15：AdminStaffFeatureTest"
php artisan test tests/Feature/Attendance --filter=AdminStaffFeatureTest  --stop-on-failure || exit 1
echo "▶︎ 16：AdminCorrectionRequestFeatureTest"
php artisan test tests/Feature/Attendance --filter=AdminCorrectionRequestFeatureTest  --stop-on-failure || exit 1

echo "All tests completed."
