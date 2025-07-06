# アプリケーション名
　勤怠アプリ
## 環境構築
　Dockerビルド
　　1. git clone git@github.com:hshino0561/mock_coachtech_kintai.git
　　※クローン先ディレクトリは適宜用意お願いします。
　　2. docker-compose up -d --build
　　※各種コンテナが起動しない場合は、利用環境に合わせてdocker-compose.ymlファイルを編集してください。

　Laravel環境構築
　　1. docker-compose exec php bash
　　2. composer install
　　3. .env.exampleファイルから.envを作成し、環境変数を変更
　　    cp src/.env.example src/.env
　　    ※環境により適宜変更してください。
　　    ※参考変更例
　　    DB_CONNECTION=mysql
　　    DB_HOST=mysql
　　    DB_PORT=3306
　　    DB_DATABASE=laravel_db
　　    DB_USERNAME=laravel_user
　　    DB_PASSWORD=laravel_pass
　　    
　　    MAIL_FROM_ADDRESS=no-reply@example.com
　　    MAIL_FROM_NAME="COACHTECH"
　　4. php artisan key:generate
　　5. docker-compose up -d --build
　　　※または下記を実行してください。
　　　　docker-compose down
　　　　docker-compose up -d
　　6. php artisan migrate
　　7. php artisan db:seed
　　8. php artisan storage:link

## テスト：Feature　※機能テストで確認しました。
　　0. 一括実行(シェル)　※コンテナ内で実行
　　　 sh tests/Feature/FeatureTest.sh
　※一括実行でうまくいかない場合は、下記単体版を実行してください。
　　　また、エラー時は、必要により適宜データのリセット後に再実行をお願いします。
　　1. 会員登録機能(一般ユーザ)
　　　 php artisan test tests/Feature/Auth --filter=UserRegisterTest
　　2. メール認証機能(一般ユーザ)
　　　 php artisan test tests/Feature/Auth --filter=EmailVerificationTest
　　3. ログイン機能(一般ユーザ)
　　　 php artisan test tests/Feature/Auth --filter=LoginTest
　　4. ログイン機能(管理者)
　　　 php artisan test tests/Feature/Auth --filter=AdminLoginTest
　　5. 日時取得機能
　　　 php artisan test tests/Feature/Attendance --filter=CurrentDatetimeDisplayTest
　　6. ステータス確認機能
　　　 php artisan test tests/Feature/Attendance --filter=WorkStatusDisplayTest
　　7. 出勤機能
　　　 php artisan test tests/Feature/Attendance --filter=AttendanceStartTest
　　8. 休憩機能
　　　 php artisan test tests/Feature/Attendance --filter=BreakFeatureTest
　　9. 退勤機能
　　　 php artisan test tests/Feature/Attendance --filter=LeaveFeatureTest
　  10. 勤怠一覧情報取得機能（一般ユーザー）
　　　 php artisan test tests/Feature/Attendance --filter=AttendanceListFeatureTest
　  11. 勤怠詳細情報取得機能（一般ユーザー）
　　　 php artisan test tests/Feature/Attendance --filter=AttendanceDetailFeatureTest
　  12. 勤怠詳細情報修正機能（一般ユーザー）
　 　  php artisan test tests/Feature/Attendance --filter=AttendanceCorrectionFeatureTest
　  13. 勤怠一覧情報取得機能（管理者）
　 　  php artisan test tests/Feature/Attendance --filter=AdminAttendanceListFeatureTest 
　  14. 勤怠詳細情報取得・修正機能（管理者）
　 　  php artisan test tests/Feature/Attendance --filter=AdminAttendanceDetailFeatureTest
　  15. ユーザー情報取得機能（管理者）
　 　  php artisan test tests/Feature/Attendance --filter=AdminStaffFeatureTest
　  16. 勤怠情報修正機能（管理者）
　 　  php artisan test tests/Feature/Attendance --filter=AdminCorrectionRequestFeatureTest

## 使用技術(実行環境)
　　・PHP 8.1.1
　　・Laravel 10.48.28
　　　・fortify
　　　・formrequest
　　　・mailhog　：US002：FN011：メールを用いた認証機能
　　・MySQL 8.0.26
　　・Nginx 1.21.1
　　・Bootstrap
　　・PHPFeature

## ER図
　　・ER.drawio.svg

## URL
　　・開発環境(一般ユーザ用ログイン画面)：http://localhost/login/
　　・開発環境(管理者用ログイン画面)：http://localhost/admin/login/
　　・phpMyAdmin：http://localhost:8080

## 管理者用ダミーユーザ　※パスワードは「pass」※模擬案件のため、パスワード明記します。
　　・admin1@admin.com
　　・admin2@admin.com

## 要件修正内容　※一部コーチへ確認のうえ変更。その他細かい点はスプレッドシート資料をご参照ください。
　　・【機能要件】11：管理者ユーザーは各勤怠の詳細を確認・修正することができる：FN039：バリデーション機能
　　　・出勤時間もしくは退勤時間が不適切な値です。
　　　　⇒上記など末尾に「。」が付いているが、一般ユーザ用と差異あり。機能要件の通り「。」付きで実装

　　・【テストケース】13：勤怠詳細情報取得・修正機能（管理者）
　　　①出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
　　　「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
　　　　⇒機能要件に合わせ末尾は「。」付きにて実装
　　　②休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
　　　「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
　　　　⇒「休憩時間が勤務時間外です。」※機能要件に合わせてメッセージ内容を修正して実装
　　　③休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
　　　「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
　　　　⇒「休憩時間が勤務時間外です。」※機能要件に合わせてメッセージ内容を修正して実装
　　　④備考欄が未入力の場合のエラーメッセージが表示される
　　　「備考を記入してください」というバリデーションメッセージが表示される
　　　　⇒機能要件に合わせ末尾は「。」付きにて実装

　　・【テストケース】11：勤怠詳細情報修正機能（一般ユーザー）
　　　①休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
　　　「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
　　　　⇒「休憩時間が勤務時間外です」※機能要件に合わせてメッセージ内容を修正して実装
　　　②休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
　　　「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
　　　　⇒「休憩時間が勤務時間外です」※機能要件に合わせてメッセージ内容を修正して実装

　　・承認済み一覧に表示の詳細画面を表示した際、一般ユーザのみ右下に下記の表示がされる
　　　「※承認待ちのため修正はできません。」
　　　⇒要件が見当たらずREADMEに記載することでコーチと合意

## 調整、確認不足内容、補足
　　・管理者用ダミーユーザをコーチ相談のうえ、2ユーザ作成
　　・デザイン調整、レスポンシブ対応
　　・コード整理
　　・環境変数について、セキュリティ観点でよくありませんが、内部で連携する手段など確認不足のため、今回はREADME内に記載します。
　　・要件に明記されていない点を一部シーダファイルを作成済みです。

## 未実装内容
　　・未実装機能はない認識です。
