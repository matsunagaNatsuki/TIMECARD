# TIMECARD

従業員の出退勤・休憩時間を記録し、勤怠情報の確認や修正申請を行える勤怠管理システムです。
管理者は従業員ごとの勤怠確認、修正申請の承認、月次データのCSV出力を行えます。

## このプロジェクトについて

本プロジェクトは、プログラミングスクールの学習課題として、提示された要件定義・画面設計をもとに制作したWebアプリケーションです。

Laravelを用いたMVC構成をはじめ、認証・認可、データベース設計、バリデーション、テスト、Dockerによる開発環境の構築まで、Webアプリケーション開発の一連の工程に取り組みました。

## 主な機能

### 一般ユーザー

- 会員登録・ログイン・メール認証
- 出勤、退勤、休憩開始、休憩終了の打刻
- 月ごとの勤怠一覧表示
- 勤怠詳細の確認
- 出退勤時刻・休憩時刻の修正申請
- 修正申請の状態確認

### 管理者

- 管理者ログインと権限によるアクセス制御
- 日付ごとの全スタッフの勤怠一覧表示
- スタッフ一覧およびスタッフ別の月次勤怠表示
- 勤怠情報の修正
- 修正申請の確認・承認
- スタッフ別月次勤怠データのCSV出力

## 使用技術

- PHP 8.1
- Laravel 8.83.29
- MySQL 8.0.26
- Nginx
- Laravel Fortify
- PHPUnit
- Docker / Docker Compose
- MailHog
- HTML / CSS

## 環境構築

### 前提

- Gitがインストールされていること
- Docker Desktopが起動していること
- `make`コマンドを利用できること

### セットアップ

リポジトリをクローンし、プロジェクト直下へ移動します。

```bash
git clone git@github.com:matsunagaNatsuki/TIMECARD.git
cd TIMECARD
```

次のコマンドでDockerイメージのビルド、依存パッケージのインストール、マイグレーション、初期データの投入を行います。

```bash
make init
```

セットアップ完了後、以下のURLへアクセスします。

- アプリケーション: http://localhost/
- phpMyAdmin: http://localhost:8080/
- MailHog: http://localhost:8025/

## テスト用データベース

MySQLコンテナへ入り、rootユーザーでMySQLにログインします。パスワードは `root` です。

```bash
docker compose exec mysql bash
mysql -u root -p
```

テスト用データベースを作成します。

```sql
CREATE DATABASE demo_test;
```

テストを実行します。

```bash
docker compose exec php php artisan test
```

## メール認証

開発環境ではMailHogを使用します。必要に応じて `src/.env` のメール設定を以下のように変更してください。

```env
MAIL_MAILER=smtp
MAIL_HOST=mail
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=任意のメールアドレス
MAIL_FROM_NAME="${APP_NAME}"
```

会員登録後、MailHog（http://localhost:8025/）で認証メールを確認できます。

## 管理者テストアカウント

```text
name: 松永 菜月
email: test@gmail.com
password: password
```

管理者アカウントは一般ユーザー画面にもログインできます。新規登録したユーザーには一般ユーザー権限が付与され、管理者画面へはアクセスできません。

## 課題仕様に対する調整事項

以下は、スクール運営へ確認・許可を得たうえで調整した内容です。

1. 管理者ログインは、管理者権限を持つアカウントだけが利用できるようミドルウェアで制御しています。
2. 初期データは2025年7月〜9月分を用意しています。
3. 管理者側の勤怠詳細では、承認待ちの場合にユーザーが申請した時刻を表示します。
4. 一般ユーザー側では、管理者が承認するまで元の勤怠情報を表示し、承認後に申請内容を反映します。
5. 承認済みの勤怠詳細画面には、承認完了を示すメッセージを表示します。
6. 当初の画面設計・基本設計書に記載されたパスを、以下の管理者用URLへ変更しています。

| 画面 | URL |
| --- | --- |
| 勤怠一覧 | `/admin/attendances` |
| 勤怠詳細 | `/admin/attendances/{id}` |
| スタッフ一覧 | `/admin/users` |
| スタッフ別勤怠一覧 | `/admin/users/{user}/attendances` |
| 申請一覧 | `/admin/requests` |
| 修正申請承認 | `/admin/requests/{id}` |
