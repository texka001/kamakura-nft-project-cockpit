# 順位表表示用タイトルフィールドの追加計画

順位表管理画面にて、「第X節終了時点」などの表示タイトルの設定を可能にするため、新しい入力フィールドを追加します。

## Proposed Changes

### KMNFT_User_Manager クラスの変更

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **テーブル定義の更新**:
  `ensure_standings_table` に `display_title varchar(255) DEFAULT '' NOT NULL` を追加します。

- **保存処理の更新**:
  `process_standings_save` で `$_POST['display_title']` を取得・サニタイズし、データベースに保存します。

- **UI の追加**:
  - `render_standings_page` のフォーム内、「Announcement Date」の直後に「Display Title」入力フィールド（テキスト）を追加します。
  - 同画面の履歴一覧テーブルに「Display Title」列を追加し、設定されたタイトルが表示されるようにします。

## Verification Plan

### Manual Verification
1.  順位表管理画面（KMNFT Console > Standings）を開きます。
2.  「Announcement Date」の下に「Display Title」フィールドがあることを確認します。
3.  適当な日付とタイトル（例：「第3節終了時点」）を入力して保存します。
4.  「History」一覧に、入力したタイトルが表示されていることを確認します。
5.  編集（Edit）ボタンを押し、既存のタイトルが正しく読み込まれることを確認します。
