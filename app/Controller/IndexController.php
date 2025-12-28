<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use Hyperf\Contract\ContainerInterface;
use Hyperf\View\RenderInterface;

class IndexController extends AbstractController
{
    protected $render;

    public function __construct(ContainerInterface $container)
    {
        $this->render = $container->get(RenderInterface::class);
    }

    public function index()
    {
        return $this->render->render('home', ['name' => 'Mundo']);
    }
}
