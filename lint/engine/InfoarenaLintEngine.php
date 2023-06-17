<?php

/*
 * Copyright 2012 Bogdan-Cristian Tătăroiu
 * Copyright 2012 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Infoarena's lint engine based on the PhutilLintEngine in Arcanist.
 * The PhutilLintEngine was altered so that certain currently unfixable tests
 * are disabled. The engine will ignore any external libraries we use, which
 * are unfortunately quite spread out.
 *
 * @group linter
 */
final class InfoarenaLintEngine extends ArcanistLintEngine {

    public function buildLinters() {
        $linters = array();

        $paths = $this->getPaths();

        // Remaining lint engines operate on file contents and ignore removed
        // files.
        foreach ($paths as $key => $path) {
            if (!$this->pathExists($path)) {
                unset($paths[$key]);
                continue;
            }
            // Don't run lint on third-party or junk stuff.
            if ($this->isExternalLibrary($path)) {
                unset($paths[$key]);
            }
        }

        $generated_linter = new ArcanistGeneratedLinter();
        $linters[] = $generated_linter;

        $nolint_linter = new ArcanistNoLintLinter();
        $linters[] = $nolint_linter;

        $text_linter = new ArcanistTextLinter();
        $text_linter->setCustomSeverityMap(
            array(
                // Disable character set linting which warns on Romanian chars
                ArcanistTextLinter::LINT_BAD_CHARSET
                => ArcanistLintSeverity::SEVERITY_DISABLED,
            ));
        $view_text_linter = new ArcanistTextLinter();
        $view_text_linter->setCustomSeverityMap(
            array(
                // Disable character set linting which warns on Romanian chars
                ArcanistTextLinter::LINT_BAD_CHARSET
                => ArcanistLintSeverity::SEVERITY_DISABLED,
                // Views are allowed to have more than 80 characters per line
                ArcanistTextLinter::LINT_LINE_WRAP
                => ArcanistLintSeverity::SEVERITY_DISABLED,
            ));

        $linters[] = $text_linter;

        $spelling_linter = new ArcanistSpellingLinter();
        $linters[] = $spelling_linter;
        foreach ($paths as $path) {
            $is_text = false;
            if (preg_match('/\.(php|css|js|hpp|cpp|l|y)$/', $path)) {
                $is_text = true;
            }
            if ($is_text) {
                $generated_linter->addPath($path);
                $generated_linter->addData($path, $this->loadData($path));

                $nolint_linter->addPath($path);
                $nolint_linter->addData($path, $this->loadData($path));

                if ($this->isViewFile($path)) {
                    $view_text_linter->addPath($path);
                    $view_text_linter->addData($path, $this->loadData($path));
                } else {
                    $text_linter->addPath($path);
                    $text_linter->addData($path, $this->loadData($path));
                }

                $spelling_linter->addPath($path);
                $spelling_linter->addData($path, $this->loadData($path));
            }
        }

        $name_linter = new ArcanistFilenameLinter();
        $linters[] = $name_linter;
        foreach ($paths as $path) {
            $name_linter->addPath($path);
        }

        $xhpast_linter = new ArcanistXHPASTLinter();
        $view_xhpast_linter = new ArcanistXHPASTLinter();
        $view_xhpast_linter->setCustomSeverityMap(
            array(
                // FIXME: Remove once code is fully ported to XHP
                ArcanistPHPOpenTagXHPASTLinterRule::ID
                => ArcanistLintSeverity::SEVERITY_DISABLED,
                ArcanistPHPCloseTagXHPASTLinterRule::ID
                => ArcanistLintSeverity::SEVERITY_DISABLED,
                ArcanistPHPShortTagXHPASTLinterRule::ID
                => ArcanistLintSeverity::SEVERITY_DISABLED,
                ArcanistPHPEchoTagXHPASTLinterRule::ID
                => ArcanistLintSeverity::SEVERITY_DISABLED,
            ));
        $linters[] = $xhpast_linter;
        $linters[] = $view_xhpast_linter;
        foreach ($paths as $path) {
            if (!preg_match('/\.php$/', $path)) {
                continue;
            }

            if ($this->isViewFile($path)) {
                $view_xhpast_linter->addPath($path);
                $view_xhpast_linter->addData($path, $this->loadData($path));
            } else {
                $xhpast_linter->addPath($path);
                $xhpast_linter->addData($path, $this->loadData($path));
            }
        }

        return $linters;
    }

    private function isExternalLibrary($path) {
        $to_skip = array('@^arcanist/@', '@^libphutil/@',
                         '@^common/external_libs/@',
                         '@^smf/@',
                         '@^junk/@',
                         '@^www/static/js/highlight@',
                         '@^www/static/css/highlight@',
        );
        foreach ($to_skip as $skip) {
            if (preg_match($skip, $path)) {
                return true;
            }
        }
        return false;
    }

    private function isViewFile($path) {
        return preg_match('@^www/views/@', $path);
    }

}
