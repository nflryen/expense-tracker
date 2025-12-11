            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<?php if (isset($additional_js)): ?>
    <?php echo $additional_js; ?>
<?php endif; ?>

<script>
    // Toggle sidebar for mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
</script>

<?php if (isset($custom_scripts)): ?>
    <script>
        <?php echo $custom_scripts; ?>
    </script>
<?php endif; ?>

</body>
</html>