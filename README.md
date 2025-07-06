# アプリケーション名
　勤怠アプリ
## 環境構築

Dockerビルド</br>
1. git clone git@github.com:hshino0561/mock_coachtech_kintai.git</br>
※クローン先ディレクトリは適宜用意お願いします。
2. docker-compose up -d --build</br>
※各種コンテナが起動しない場合は、利用環境に合わせてdocker-compose.ymlファイルを編集してください。

## Laravel環境構築</br>
1. docker-compose exec php bash</br>
2. composer install</br>
3. .env.exampleファイルから.envを作成し、環境変数を変更</br>
cp src/.env.example src/.env</br>
※環境により適宜変更してください。</br>
※参考変更例</br>
DB_CONNECTION=mysql</br>
DB_HOST=mysql</br>
DB_PORT=3306</br>
DB_DATABASE=laravel_db</br>
DB_USERNAME=laravel_user</br>
DB_PASSWORD=laravel_pass</br>
　　    
MAIL_FROM_ADDRESS=no-reply@example.com</br>
MAIL_FROM_NAME="COACHTECH"</br>
4. php artisan key:generate
5. docker-compose up -d --build</br>
※または下記を実行してください。</br>
docker-compose down</br>
docker-compose up -d</br>
6. php artisan migrate
7. php artisan db:seed
8. php artisan storage:link

## テスト：Feature
※機能テストで確認しました。</br>
0. 一括実行(シェル)　※コンテナ内で実行</br>
&nbsp;&nbsp;&nbsp;sh tests/Feature/FeatureTest.sh</br>
&nbsp;&nbsp;&nbsp;※一括実行でうまくいかない場合は、下記単体版を実行してください。</br>
&nbsp;&nbsp;&nbsp;また、エラー時は、必要により適宜データのリセット後に再実行をお願いします。</br>
1. 会員登録機能(一般ユーザ)</br>
php artisan test tests/Feature/Auth --filter=UserRegisterTest</br>
2. メール認証機能(一般ユーザ)</br>
php artisan test tests/Feature/Auth --filter=EmailVerificationTest</br>
3. ログイン機能(一般ユーザ)</br>
php artisan test tests/Feature/Auth --filter=LoginTest</br>
4. ログイン機能(管理者)</br>
php artisan test tests/Feature/Auth --filter=AdminLoginTest</br>
5. 日時取得機能</br>
php artisan test tests/Feature/Attendance --filter=CurrentDatetimeDisplayTest</br>
6. ステータス確認機能</br>
php artisan test tests/Feature/Attendance --filter=WorkStatusDisplayTest</br>
7. 出勤機能</br>
php artisan test tests/Feature/Attendance --filter=AttendanceStartTest</br>
8. 休憩機能</br>
php artisan test tests/Feature/Attendance --filter=BreakFeatureTest</br>
9. 退勤機能</br>
php artisan test tests/Feature/Attendance --filter=LeaveFeatureTest</br>
10. 勤怠一覧情報取得機能（一般ユーザー）</br>
php artisan test tests/Feature/Attendance --filter=AttendanceListFeatureTest</br>
11. 勤怠詳細情報取得機能（一般ユーザー）</br>
php artisan test tests/Feature/Attendance --filter=AttendanceDetailFeatureTest</br>
12. 勤怠詳細情報修正機能（一般ユーザー）</br>
php artisan test tests/Feature/Attendance --filter=AttendanceCorrectionFeatureTest</br>
13. 勤怠一覧情報取得機能（管理者）</br>
php artisan test tests/Feature/Attendance --filter=AdminAttendanceListFeatureTest </br>
14. 勤怠詳細情報取得・修正機能（管理者）</br>
php artisan test tests/Feature/Attendance --filter=AdminAttendanceDetailFeatureTest</br>
15. ユーザー情報取得機能（管理者）</br>
php artisan test tests/Feature/Attendance --filter=AdminStaffFeatureTest</br>
16. 勤怠情報修正機能（管理者）</br>
php artisan test tests/Feature/Attendance --filter=AdminCorrectionRequestFeatureTest

## 使用技術(実行環境)
&nbsp;&nbsp;&nbsp;・PHP 8.1.1</br>
&nbsp;&nbsp;&nbsp;・Laravel 10.48.28</br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;・fortify</br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;・formrequest</br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;・mailhog　：US002：FN011：メールを用いた認証機能</br>
&nbsp;&nbsp;&nbsp;・MySQL 8.0.26</br>
&nbsp;&nbsp;&nbsp;・Nginx 1.21.1</br>
&nbsp;&nbsp;&nbsp;・Bootstrap</br>
&nbsp;&nbsp;&nbsp;・PHPFeature</br>

## ER図
&nbsp;&nbsp;&nbsp;・ER.drawio.svg</br>

## URL
&nbsp;&nbsp;&nbsp;・開発環境(一般ユーザ用ログイン画面)：http://localhost/login/</br>
&nbsp;&nbsp;&nbsp;・開発環境(管理者用ログイン画面)：http://localhost/admin/login/</br>
&nbsp;&nbsp;&nbsp;・phpMyAdmin：http://localhost:8080</br>

## 管理者用ダミーユーザ
※パスワードは「pass」※模擬案件のため、パスワード明記します。</br>
&nbsp;&nbsp;&nbsp;・admin1@admin.com</br>
&nbsp;&nbsp;&nbsp;・admin2@admin.com

## 要件修正内容
※一部コーチへ確認のうえ変更。その他細かい点はスプレッドシート資料をご参照ください。</br>
【機能要件】11：管理者ユーザーは各勤怠の詳細を確認・修正することができる：FN039：バリデーション機能</br>
&nbsp;&nbsp;&nbsp;・出勤時間もしくは退勤時間が不適切な値です。</br>
&nbsp;&nbsp;&nbsp;⇒上記など末尾に「。」が付いているが、一般ユーザ用と差異あり。機能要件の通り「。」付きで実装

【テストケース】13：勤怠詳細情報取得・修正機能（管理者）</br>
&nbsp;&nbsp;&nbsp;①出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;⇒機能要件に合わせ末尾は「。」付きにて実装</br>

&nbsp;&nbsp;&nbsp;②休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;⇒「休憩時間が勤務時間外です。」※機能要件に合わせてメッセージ内容を修正して実装

&nbsp;&nbsp;&nbsp;③休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;⇒「休憩時間が勤務時間外です。」※機能要件に合わせてメッセージ内容を修正して実装</br>

&nbsp;&nbsp;&nbsp;④備考欄が未入力の場合のエラーメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;「備考を記入してください」というバリデーションメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;⇒機能要件に合わせ末尾は「。」付きにて実装

【テストケース】11：勤怠詳細情報修正機能（一般ユーザー）</br>
&nbsp;&nbsp;&nbsp;①休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;⇒「休憩時間が勤務時間外です」※機能要件に合わせてメッセージ内容を修正して実装

&nbsp;&nbsp;&nbsp;②休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される</br>
&nbsp;&nbsp;&nbsp;⇒「休憩時間が勤務時間外です」※機能要件に合わせてメッセージ内容を修正して実装

・承認済み一覧に表示の詳細画面を表示した際、一般ユーザのみ右下に下記の表示がされる</br>
「※承認待ちのため修正はできません。」</br>
&nbsp;&nbsp;&nbsp;⇒要件が見当たらず、そのままの表記とする旨をREADMEに記載することでコーチと合意

## 調整、確認不足内容、補足
・管理者用ダミーユーザをコーチ相談のうえ、2ユーザ作成</br>
・デザイン調整、レスポンシブ対応</br>
・コード整理</br>
・環境変数について、セキュリティ観点でよくありませんが、内部で連携する手段など確認不足のため、今回はREADME内に記載します。</br>
・要件に明記されていない点を一部シーダファイルを作成済みです。

## 未実装内容
・未実装機能はない認識です。
