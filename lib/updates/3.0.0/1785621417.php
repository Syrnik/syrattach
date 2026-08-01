<?php
/**
 * Removes obsolete files left over from earlier plugin versions.
 *
 * The Webasyst installer unpacks a new release over the old one and never
 * deletes files that disappeared from the distribution, so files dropped
 * from the repository keep lying around in existing installations until a
 * migration removes them explicitly.
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 *
 * @var shopSyrattachPlugin $this
 */

$obsolete_files = [
    // Trimmed copy of blueimp jQuery File Upload UI Plugin 6.0.2. Never
    // loaded by any template and unusable anyway: the blueimp core it
    // extends was not part of the distribution.
    'js/fileupload-widget.js',
    // Superseded by js/loadcontent.js.
    'js/loadcontent_hack.js',
    // Folded into shopSyrattachPlugin.
    'lib/shopSyrattachPluginHelper.class.php',
    // Stylesheet with a single rule for an element id that no longer
    // exists, plus Stylus/SCSS sources that predate the current build.
    'css/syrattach.css',
    'css/syrattach.scss',
    'css/syrattach.styl',
    'css/syrattach-prod.styl',
];

foreach ($obsolete_files as $relative_path) {
    // waFiles::delete() is a no-op for a path that does not exist, so a clean
    // installation needs no special handling. A failure, on the other hand,
    // must not abort the whole plugin update: an exception thrown from a
    // migration propagates out of waPlugin::__construct().
    try {
        waFiles::delete($this->path . '/' . $relative_path);
    } catch (Exception $e) {
        waLog::log(
            sprintf('SyrAttach: cannot delete obsolete file "%s": %s', $relative_path, $e->getMessage()),
            shopSyrattachPlugin::LOG
        );
    }
}
