    <button id="helpPopoverButton" type="button" class="info-btn" aria-expanded="false" aria-controls="helpPopover">
        <i class="bi bi-question-circle"></i>
    </button>
    <div id="helpPopover" class="popover-box" role="tooltip" aria-hidden="true">
        Proyecto de Chatbot desarrollado por estudiantes de 7mo año I de la EPET3
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var button = document.getElementById('helpPopoverButton');
            var popover = document.getElementById('helpPopover');

            function closePopover() {
                popover.classList.remove('show');
                button.setAttribute('aria-expanded', 'false');
                popover.setAttribute('aria-hidden', 'true');
            }

            button.addEventListener('click', function (event) {
                event.stopPropagation();
                var isOpen = popover.classList.toggle('show');
                button.setAttribute('aria-expanded', isOpen.toString());
                popover.setAttribute('aria-hidden', (!isOpen).toString());
            });

            document.addEventListener('click', function (event) {
                if (!popover.contains(event.target) && !button.contains(event.target)) {
                    closePopover();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closePopover();
                }
            });
        });
    </script>
</body>
</html>