<script src="<?= env('ASSET_URL') ?>/admin_assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/libs/apexcharts/apexcharts.min.js"></script>
  <script src="<?= env('ASSET_URL') ?>/admin_assets/libs/flatpickr/flatpickr.min.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/dashboard.js?v=<?= filemtime(base_path('assets/admin_assets/js/dashboard.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/notifications.js?v=<?= filemtime(base_path('assets/admin_assets/js/notifications.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/menu_counts.js?v=<?= filemtime(base_path('assets/admin_assets/js/menu_counts.js')) ?>" data-url="<?= url('api/menu_counts.php') . (str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '?scope=manage' : '') ?>"></script>

</body>

</html>