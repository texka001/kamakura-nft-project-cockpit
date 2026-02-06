# 順位表編集UI改善の実装計画

順位表の編集画面において、現在の操作が「新規作成」ではなく「更新」であることを分かりやすくし、かつデータの整合性を保つために日付の変更を制限します。

## Proposed Changes

### KMNFT_User_Manager クラスの変更

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **タイトルの強調**:
  編集モード（Update Standings）の場合に、背景色とボーダーを追加して視覚的に強調します。
  ```html
  <h2 style="<?php echo $edit_item ? 'background: #f0f6fb; border-left: 4px solid #2271b1; padding: 10px; margin-left: -20px; margin-right: -20px; margin-top: -20px; margin-bottom: 20px;' : ''; ?>">
      <?php echo $edit_item ? 'Update Standings' : 'Add New Standings'; ?>
  </h2>
  ```

- **日付フィールドの固定**:
  編集モード時は日付を変更できないよう `readonly` 属性を追加します。また、見た目でも変更不可であることがわかるよう、背景色を少しグレーにします。
  ```html
  <input type="date" name="announcement_date" id="announcement_date" required
      value="<?php echo $edit_item ? esc_attr($edit_item->announcement_date) : date('Y-m-d'); ?>"
      <?php echo $edit_item ? 'readonly style="background-color: #f0f0f1; cursor: not-allowed;"' : ''; ?>>
  ```

## Verification Plan

### Manual Verification
1.  「Edit」ボタンをクリックして編集画面に入ります。
2.  「Update Standings」というタイトルが青い左ボーダーと背景色で強調されていることを確認します。
3.  「Announcement Date」フィールドがグレーアウトし、値を変更できない（読み取り専用）になっていることを確認します。
4.  「Cancel」を押して戻り、今度は新規作成状態でタイトルが通常通りであることを確認します。
