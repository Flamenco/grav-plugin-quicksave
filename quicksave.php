<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 TwelveTone LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Twelvetone\Common\ServiceManager;

/**
 * Class QuicksavePlugin
 * @package Grav\Plugin
 */
class QuicksavePlugin extends Plugin
{
	public static function getSubscribedEvents()
	{
		return [
			'onPluginsInitialized' => ['onPluginsInitialized', 0]
		];
	}

	public function onPluginsInitialized()
	{
		if (!isset($this->grav['core-service-util'])) {
			return;
		}

		if (!$this->isAdmin()) {
			return;
		}

		if (!$this->grav['core-service-util']->checkPluginDependencies($this)) {
			return;
		}

		$this->grav['core-service-util']->checkAllPluginDependencies();

		$this->enable([
			'onPageNotFound' => ['onPageNotFound', 1],
			'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0],
		]);

		$manager = ServiceManager::getInstance();
		$manager->registerService('action', [
			'render' => function () {
				return '
                 <span style="display:inline-block">
                    <form id="saveajax" style="display: inline-block">
                        <button class="button multiline">
                            <i class="fa fa-check-square"></i>Save<br />Content</button>
                    </form>
                    <div id="healthy_snackbar"></div>
                </span>
                ';
			},
			'isEnabled' => function ($context) {
				return $context && $context->exists();
			},
			'scope' => ['page'],
			'order' => 'last'
		]);

		$manager->registerService('asset', [
			'type' => 'css',
			'url' => "plugins://quicksave/admin/assets/healthy_snackbar.css",
			'scope' => ['page'],
			'order' => 'first'
		]);

		$manager->registerService('asset', [
			'type' => 'javascript',
			'url' => "plugins://quicksave/admin/assets/healthy_snackbar.js",
			'scope' => ['page'],
			'order' => 'first'
		]);

		$manager->registerService("asset", [
			'type' => 'twig',
			'url' => "quicksave_ajax.html.twig",
			'scope' => ['page'],
			'order' => 'last'
		]);

		$manager->registerService("asset", [
			'type' => 'css',
			'url' => "plugins://quicksave/admin/assets/quicksave.css",
			'scope' => ['page'],
			'order' => 'last'
		]);

		if ($this->config->get('plugins.quicksave.enable_keyboard_shortcut', false)) {
			$manager->registerService("asset", [
				'type' => 'js',
				'url' => "plugins://quicksave/admin/assets/quicksave_hotkey_s.js",
				'scope' => ['page'],
				'order' => 'first'
			]);
		}
		if ($this->config->get('plugins.quicksave.enable_keyboard_shortcut_a', false)) {
			$manager->registerService("asset", [
				'type' => 'js',
				'url' => "plugins://quicksave/admin/assets/quicksave_hotkey_a.js",
				'scope' => ['page'],
				'order' => 'first'
			]);
		}
//        $manager->registerService("key", [
//            'scope' => ['page'],
//            'keys' => ['s'],
//            'modifiers' => ['shift', 'meta'],
//            'clientCallback' => 'alert("ok")',
//        ]);
	}

	public function onAdminTwigTemplatePaths($event)
	{
		$event['paths'] = array_merge($event['paths'], [__DIR__ . '/admin/templates']);
		return $event;
	}

	public function onPageNotFound($e)
	{
		//TODO should never happen, right?
		if (!$this->isAdmin()) {
			return;
		}

		$route = $this->grav['admin']->location . "/" . $this->grav['admin']->route;

		switch ($route) {
			// AJAX content
			case "save-content/":
				$this->checkPermissions($route);
				/*
				 * payload is JSON with {route:"...", content:"..."}
				 */
				$rawData = file_get_contents("php://input");
				$req = json_decode($rawData, true);
				$route = $req['route'];
				$content = $req['content'];

				if ($route === "") {
					self::result("ERROR", "The route was not set.");
				}

				// As of Grav 1.7, the pages are not present unless this is called
				if (method_exists($this->grav['admin'], 'enablePages')) {
					$this->grav['admin']->enablePages();
				}
				$page = $this->grav['pages']->find($route, true);
				if (!$page) {
					self::result("ERROR", "The target page '$route' was not found");
				}

				$curContent = $page->rawMarkdown();
				if (strcmp($content, $curContent) == 0) {
					self::result("OK", "The content has not changed.");
				}

				$page->content($content);
				$page->save();
				$this->grav['core-service-util']->save($page);

				if ($this->grav['config']->get("plugins.quicksave.clear_dirty")) {
					//TODO implement clear_dirty
				}

				self::result("OK", "The content was saved.");
				break;

			default:
				break;
		}
	}

	/**
	 * @param $status "OK" or "ERROR"
	 * @param string $message
	 */
	static function result($status, $message = "")
	{
		header('HTTP/1.1 200 OK');
		$res = ["status" => $status, "message" => $message];
		die(json_encode($res));
	}

	private function checkPermissions($route)
	{
		if (!$this->grav['admin']->authorize([
			"admin.super",
			"admin.pages",
			"plugin.quicksave.content"
		])) {
			self::result("ERROR", "Permission denied.");
		}
	}
}

