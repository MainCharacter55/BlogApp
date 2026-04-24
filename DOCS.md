1. 提出課題の概要 / Project Overview
本プロジェクトは、API提供だけでなく、ユーザーが直接利用できるGUI（Blade）を備えたフルスタックのブログアプリケーション 「BlogApp」 として構築しました。

取り組んだ課題
基本課題 (Basic Tasks): [完了]
コメント機能の完全なCRUD実装（API）。
MySQLを使用したリレーショナルなDB設計（Users, Posts, Comments）。
Swagger UI / ReDoc によるAPIドキュメントの自動生成。

応用課題 (Applied Tasks): [完了]
ユーザー認証: Laravel Sanctum を使用したセキュアな認証。
二段階登録: メールトークンを使用した会員登録フローの実装。
GUI実装: Laravel Blade を使用し、直感的に操作できるフロントエンドを構築。
追加機能: 投稿やコメントに対するリアクション（いいねなど）機能、およびコメントへの返信（ネスト）機能。

2. 工夫点・アピールポイント (Highlights)
プロダクションを意識したセキュリティ:
WebとAPIの両方でレートリミット（Throttle）を設定。
会員登録時のパスワードポリシーを共通化し、安全性を向上させました。
BOLA（認可）対策として、自分以外のコメントの編集・削除をPolicyで厳密に禁止。

開発環境のこだわり:
Docker Sailを拡張し、ドキュメント閲覧用に ReDoc コンテナを追加。
WSL環境でのファイルパーミッション問題やDocker認証エラーを自力で解決し、安定した開発基盤を構築しました。

将来的な拡張性:
単なる課題に留めず、将来的に自身のPython製ポートフォリオサイトと連携させる「個人ブログのバックエンド」として再定義し、独自リポジトリ BlogApp として管理しています。

3. 解決した問題・得た学び (Problem Solving & Learning)
Gitのトラブルシューティング:
git reset --hard による予期せぬコード紛失を経験しましたが、VS Codeの「Local History」とGitHub Copilotの履歴を駆使して復旧させました。この経験から、コミットの重要性とIDEのバックエンド機能への理解が深まりました。

マルチ言語・マルチスタックの理解:
PHP/Laravelでのバックエンド構築を通じ、Python(Django)やJava(JakartaEE)と比較した際のLaravelの規約の利便性や開発スピードの速さを実感しました。

RESTful API設計:
リソースクラス（UserResource等）を介することで、DBの構造を隠蔽しながらクライアントに必要なデータだけを返す設計手法を学びました。

4. 注意して見てほしい点 (Review Focus)
コードの共通化: AppServiceProvider でのレートリミッター定義や、バリデーションロジックの共通化に配慮しました。

ネスト構造: コメントに対する返信機能（親子関係）が正しくDBおよびAPIで処理されているかをご確認いただけますと幸いです。

---------------------------------------------------
起動・利用方法 (Usage)
起動
Bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed

アクセス
Web UI (Blade): http://localhost/

API Documentation:
Swagger: http://localhost:8002/
ReDoc: http://localhost:8003/
Mail Confirmation (Mailpit): http://localhost:8025/
---------------------------------------------------
