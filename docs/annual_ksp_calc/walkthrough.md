# Annual KSP Calculation Implementation

## 概要
ユーザーが保有するNFTトークンに基づいて、シーズン（年度）ごとのKSP獲得ポイントを集計・表示する機能を実装しました。
これにより、ユーザーは現在のシーズンだけでなく、過去のシーズンにおける貢献度も確認できるようになりました。

## 変更点

### 1. `inc/class-kmnft-user-manager.php`
- **メソッド追加**: `get_user_annual_ksp($user_id)`
    - ユーザーIDを入力として受け取り、`kmnft_holdings` テーブルから保有トークンIDを取得。
    - `kmnft_token_ksp` テーブルから、対象トークンの `acquisition_point` を `season` ごとに集計。
    - 結果をシーズン降順（最新のシーズンが先頭）で返却。

### 2. `page-dashboard.php`
- **ロジック変更**:
    - 以前の `kmnft_ksp_ledger` の単純合計クエリを廃止。
    - `KMNFT_User_Manager->get_user_annual_ksp()` を呼び出し、取得したシーズン別データの合計値を「Total KSP」として計算。
- **UI変更 (KSP Module)**:
    - 合計ポイントの下に、最新シーズンのポイントを表示。
    - 過去シーズンのデータが存在する場合、`<details>` タグを用いたアコーディオンメニュー「Past Seasons」を表示し、クリックすることで過去の履歴を展開表示可能に。

## 確認方法
1. 管理画面（Cockpit）にログイン。
2. 左カラムの「Total KSP Status」を確認。
3. 合計値の下に、最新年度（例：2026 Season）のポイントが表示されていることを確認。
4. 複数年度のデータがある場合、「Past Seasons」をクリックして過去データが表示されることを確認。

## デプロイ情報
- コミット: `Implement Annual KSP Calculation Breakdown`
- 変更ファイル:
    - `inc/class-kmnft-user-manager.php`
    - `page-dashboard.php`
