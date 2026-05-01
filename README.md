# blog-service

日本語
====

blog-service は、投稿閲覧・コメント・リアクションを中心にしたシンプルなブログアプリです。API と Blade の両方を備えており、会員登録はメールトークンを使った二段階方式です。(DOCS.mdも確認してください。)

## 主な機能

- 投稿一覧（Recent / Popular）と投稿詳細の表示
- ログイン / ログアウト
- メールトークンを使った二段階会員登録
- ログイン後のコメント投稿・返信・編集・削除
- 投稿とコメントへのリアクション
- API 経由での投稿一覧取得・投稿作成・コメント閲覧・投稿・編集・削除

## 起動方法

1. Sail を起動します。
   `./vendor/bin/sail up -d`
2. マイグレーションとシーディングを実行します。
   `./vendor/bin/sail artisan migrate --seed`
3. ブラウザで以下を開きます。
   - ブログ画面: `http://localhost`
   - ReDoc: `http://localhost:8003`
   - Swagger UI: `http://localhost:8002`
   - Mailpit: `http://localhost:8025`

## 画面

- `/` blog-service のホーム
- `/recent` 新着投稿
- `/popular` 人気投稿
- `/posts/{post}` 投稿詳細
- `/login` ログイン
- `/register` 会員登録

## Web UI について

Web 画面では投稿閲覧、コメント投稿、返信、リアクションまで利用できます。

### API ドキュメント（Swagger UI / ReDoc）

プロジェクトルートで以下を実行すると、Swagger UI と ReDoc のドキュメント用サービスのみ起動できます。

```bash
# ドキュメント用サービスのみ起動
docker compose up -d swagger redoc
```

- Swagger UI: `http://localhost:8002`（`./docs/api/${OPENAPI_FILE_NAME}` の YAML を参照）
- ReDoc: `http://localhost:8003`（同じ YAML を参照）

OpenAPI ファイル名を変更した場合は、`.env` の `OPENAPI_FILE_NAME` を `docs/api` 配下のファイル名に合わせて設定してください。

English
====

blog-service is a simple blog application centered on post browsing, comments, and reactions. It includes both API and Blade-based web pages. Registration uses a two-step email token flow.

## Features

- Post feeds (Recent and Popular) and post detail pages
- Login / logout
- Two-step registration using an email token
- Authenticated comment posting, replies, editing, and deleting
- Reactions for posts and comments
- API endpoints for post listing, post creation, and comment CRUD

## Run

1. Start Sail.
   `./vendor/bin/sail up -d`
2. Run migrations and seeders.
   `./vendor/bin/sail artisan migrate --seed`
3. Open the app in your browser.
   - Blog UI: `http://localhost`
   - ReDoc: `http://localhost:8003`
   - Swagger UI: `http://localhost:8002`
   - Mailpit: `http://localhost:8025`

### API docs (Swagger UI / ReDoc)

   You can run the docs services (Swagger UI and ReDoc) from the project root using Docker Compose:

   ```bash
   # start only the docs services
   docker compose up -d swagger redoc
   ```

   - Swagger UI will be available at `http://localhost:8002` (serves the YAML mounted from `./docs/api/${OPENAPI_FILE_NAME}`).
   - ReDoc will be available at `http://localhost:8003` (serves the same YAML).

   If you changed the OpenAPI filename, set `OPENAPI_FILE_NAME` in your `.env` to match the file under `docs/api`.

## Pages

- `/` blog-service home
- `/recent` recent posts
- `/popular` popular posts
- `/posts/{post}` post detail
- `/login` login
- `/register` registration

## Web UI

The current web interface supports post browsing, comments, replies, and reactions.
