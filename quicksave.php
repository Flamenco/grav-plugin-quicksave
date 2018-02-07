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
        if (!$this->isAdmin()) {
            return;
        }

        $this->enable([
            'onPageNotFound' => ['onPageNotFound', 1],
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0],
        ]);
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

                $page = $this->grav['pages']->find($route);
                if (!$page) {
                    self::result("ERROR", "The target page '$route' was not found");
                }

                $curContent = $page->rawMarkdown();
                if (strcmp($content, $curContent) == 0) {
                    self::result("OK", "The content has not changed.");
                }

                $page->content($content);
                $page->save();

                if ($this->grav['config']->get("plugins.quicksave.clear_dirty")) {

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
