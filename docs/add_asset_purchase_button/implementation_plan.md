# Refine "Buy Assets" Button

The user requested the "GET MORE ASSETS" button be more subtle in terms of position and size.

## User Review Required

> [!NOTE]
> Changing the button from a large gradient button to a smaller, outlined "ghost" button style to match the "Show More" buttons.

## Proposed Changes

### Theme

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- Change button styling:
  - Remove background gradient and heavy shadows.
  - Use `border border-kmnft-green` (outlined style).
  - Reduce padding to `px-6 py-2`.
  - Reduce font size to `text-sm` or `text-xs`.
  - Remove the "Visit Official Store..." text line if it adds too much noise, or keep it very subtle. (I will keep it but make sure the spacing is tight).
  - Remove the top border separator (`border-t`) to make it feel more integrated, or keep it but with less margin. I'll reduce the `mt-6` to `mt-4`.

## Verification Plan

### Manual Verification
- Deploy and check dashboard.
- Verify the button looks "otonashime" (subtle/quiet).
