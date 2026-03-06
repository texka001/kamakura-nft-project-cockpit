# Match Results Manager の画像追加ボタンの修正

Match Results Manager の「Goal Images」セクションにある「画像を追加」ボタンが機能しない問題を修正します。WordPress のメディアライブラリを利用して画像をアップロード・選択できるようにします。

## タスクリスト

- [x] 現状のコード調査
  - [x] 該当箇所の特定 (`KMNFT_User_Manager.render_match_results_page`)
  - [x] HTML/JS の詳細確認
  - [x] 管理画面での WordPress メディアライブラリの読み込み設定を確認
- [x] 実装計画の作成
- [x] 修正の実施
  - [x] メディアライブラリを起動する JavaScript の追加/修正
  - [x] WordPress メディアライブラリの読み込みスクリプトの追加
- [x] デプロイとドキュメント保存
- [/] Goal Images 説明文の更新
  - [ ] 説明文の文言修正
  - [ ] 再デプロイ
- [ ] 動作確認
