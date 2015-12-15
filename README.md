### 申請 gmail 帳號
- 取得帳號
- 取得密碼

### Environment
- PHP 5.5
- composer (https://getcomposer.org/)
- imap

### Installation
```sh
$ composer.phar self-update
$ composer.phar install
$ cp app/config/template/* app/config/
$ vi app/config/*.php
```

### Execute
```sh
$ php shell/import.php
$ php shell/get.php
```

### Information
- 不予許匯入相同的 message id, 會略過該筆資料, 不會因此而更新資料 (由 mysql 處理)

### 收發信件注意事項
- 讀取收件不要經由 gmail, 因為在 gmail 將信件設定為已讀之後, 程式將不會 import 該信件
- 發送信件不要經由 gmail, 因為在 gmail 撰寫並且寄信之後, 信件為已讀, 將無法由程式 import 該 "寄件"
- 必須經由客戶端界面編寫並發送 email, 該信件內容 "不要" 在這裡寫入資料庫
