        </div><!-- /.admin-main -->
    </main><!-- /.admin-content -->
</div><!-- /.admin-layout -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Lightweight WYSIWYG: rich editors sync their HTML into a hidden textarea.
document.querySelectorAll('.cms-toolbar button').forEach((button) => {
    button.addEventListener('click', () => {
        const editor = button.closest('.cms-field, .card-body').querySelector('.cms-rich-editor');
        if (!editor) return;
        editor.focus();
        document.execCommand(button.dataset.command, false, button.dataset.value || null);
        editor.dispatchEvent(new Event('input'));
    });
});
document.querySelectorAll('.cms-rich-editor').forEach((editor) => {
    const target = document.getElementById(editor.dataset.target);
    if (!target) return;
    editor.addEventListener('input', () => { target.value = editor.innerHTML; });
});
</script>
</body>
</html>
