<?php

namespace engine;

class NotFoundController extends Controller
{
    public function actionIndex(): Response
    {
        return $this->render('index');
    }
}