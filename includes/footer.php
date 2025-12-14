            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<?php if (isset($additional_js)): ?>
    <?php echo $additional_js; ?>
<?php endif; ?>

<script>
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