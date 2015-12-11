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
