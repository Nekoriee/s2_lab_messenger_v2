<?php

namespace Pcat\MessengerV2;

use Twig\Environment;

class Controller {
    private Environment $twig;

    public function __construct(Environment $twig) {
        $this->twig = $twig;
    }

    public function __invoke() {
        return $this->twig->render('controller.html.twig');
    }

    public function showMessages($messages) {
	return $this->twig->render('controller.html.twig', ['messages' => $messages]);
    }
}
