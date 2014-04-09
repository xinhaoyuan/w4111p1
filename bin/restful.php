<?php
interface IREST {
    public function get($args);
    public function post($args);
    public function put($args);
    public function delete($args);
    public function dispatch($path);
};

class DefaultIREST implements IREST {
    public function get($args) { return NULL; }
    public function post($args) { return NULL; }
    public function put($args) { return NULL; }
    public function delete($args) { return NULL; }
    public function dispatch($path) { return $this; }

    final protected function _parse_path($path, &$name, &$remain) {
        if (strlen($path) === 0) {
            $name = ""; $remain = "";
            return TRUE;
        }
        
        if (preg_match("|^//*([^/]*)|", $path, $matches) === FALSE)
            return FALSE;
        $name = $matches[1];
        $remain = substr($path, strlen($matches[0]));
        if (!is_string($remain)) $remain = "";
        return TRUE;
    }
};
?>
