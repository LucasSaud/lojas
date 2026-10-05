		</div>

		<script src="<?php echo $app['url']; ?>/_core/_cdn/bootstrap/js/bootstrap.min.js"></script>
		<script src="<?php echo $app['url']; ?>/_core/_cdn/sidr/js/jquery.sidr.min.js"></script>
		<script src="<?php echo $app['url']; ?>/_core/_cdn/maskMoney/js/maskmoney.min.js"></script>
		<script src="<?php echo $app['url']; ?>/_core/_cdn/maskedInput/js/jquery.maskedinput.min.js"></script>
		<script src="<?php echo $app['url']; ?>/_core/_cdn/validate/js/jquery.validate.min.js"></script>
		<script src="<?php echo $app['url']; ?>/_core/_cdn/sticky/js/jquery.sticky.min.js"></script>
		<script src="<?php echo $app['url']; ?>/_core/_cdn/app/js/template.js"></script>
		<?php system_footer(); ?>

	</body>

	<script>
        function promptInstall() {
		if('serviceWorker' in navigator) {
		  navigator.serviceWorker
		           .register('serviceworker.js')
		           .then(function() { console.log('Service Worker Registered'); });
		}
}

        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (event) => {
            // Prevenir o evento padrão para evitar a instalação automática
            event.preventDefault();

            // Armazenar o evento para acionar a instalação posteriormente
            deferredPrompt = event;

            // Exibir o botão de instalação
            document.getElementById('installButton').style.display = 'block';
        });

        // Adicionar um manipulador de clique para o botão de instalação
        document.getElementById('installButton').addEventListener('click', () => {
            if (deferredPrompt) {
                // Disparar a instalação
                deferredPrompt.prompt();

                // Ouça o resultado da instalação
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('O usuário aceitou a instalação do PWA.');
                    }
                    deferredPrompt = null;
                });
            }
        });
    </script>
</html>