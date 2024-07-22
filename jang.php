<?php

trait TokenType {
        public $t_plus = "PLUS";
        public $t_int = "INT";
        public $t_float = "FLOAT";
        public $t_string = "STRING";
        public $t_identifier = "ID";
        public $t_semicolon = "SEMICOLON";
        public $t_minus = "MINUS";
        public $t_set = "SET";
        public $t_lparen = "LPAREN";
        public $t_rparen = "RPAREN";
        public $t_call = "CALL";
        public $t_lbracket = "LBRACKET";
        public $t_rbracket = "RBRACKET";
        public $t_var = "VAR";
        public $t_times = "TIMES";
        public $t_divide = "DIVIDE";
}

class Lexer {
        use TokenType;
        public $tokens = array();
        public $code;
        private $jndtoks = array();

        public function __construct($code) {
                $this->code = $code;
        }

        public function Tokenize() {
                $pos = 0;
                while ($pos < strlen($this->code)) {
                        $token = $this->code[$pos];
                        $pos++;

                        if (is_numeric($token)) {
                                while((is_numeric($token) || $token == ".") && $pos <= strlen($this->code)) {
                                        array_push($this->jndtoks, $token);
                                        if($pos < strlen($this->code)) {
                                                $token = $this->code[$pos];
                                        }
                                        $pos++;
                                }
                                $num = join("", $this->jndtoks);
                                if(strpos($num, '.') === false) {
                                        $this->tokens[] = array($this->t_int, (int)$num);
                                } else {
                                        $this->tokens[] = array($this->t_float, (float)$num);
                                }
                                $this->jndtoks = array();
                                $pos--;
                        } else if($token == "\"") {
                                $token = $this->code[$pos];
                                $pos++;
                                while($token != "\"" && $pos < strlen($this->code)) {
                                        if ($token == "\\" && $this->code[$pos + 1] == "n") {
                                                $this->jndtoks[] = "\n";
                                                $pos++;
                                        } else {
                                                $this->jndtoks[] = $token;
                                        }
                                        $token = $this->code[$pos];
                                        $pos++;
                                }
                                $this->tokens[] = array($this->t_string, join("", $this->jndtoks));
                                $this->jndtoks = array();
                        } else if($token == "+") {
                                $this->tokens[] = array($this->t_plus, "+");
                        } else if($token == "-") {
                                $this->tokens[] = array($this->t_minus, "-");
                        } else if($token == "*") {
                                $this->tokens[] = array($this->t_times, "*");
                        } else if($token == "/") {
                                $this->tokens[] = array($this->t_divide, "/");
                        } else if($token == ";") {
                                if ($pos < strlen($this->code)) {
                                        if($this->code[$pos] == "=") {
                                                $this->tokens[] = array($this->t_set, ";=");
                                                $pos++;
                                        } else {
                                                $this->tokens[] = array($this->t_semicolon, ";");
                                        }
                                } else {
                                        $this->tokens[] = array($this->t_semicolon, ";");
                                }
                        } else if($token == " " || $token == "\n" || $token == "\t") {
                                continue;
                        } else if($token == "(") {
                                $this->tokens[] = array($this->t_lparen, "(");
                        } else if($token == ")") {
                                $this->tokens[] = array($this->t_rparen, ")");
                        } else if($token == "!") {
                                $this->tokens[] = array($this->t_call, "!");
                        } else if($token == "{") {
                                $this->tokens[] = array($this->t_lbracket, "{");
                        } else if($token == "}") {
                                $this->tokens[] = array($this->t_rbracket, "}");
                        } else if($token == "#") {
                                $this->tokens[] = array($this->t_var, "#");
                        } else {
                                while($token != " " && $token != "\n" && $token != "\t" && $token != "+" && $token != "\"" && $token != ";" && $token != "-" && $pos <= strlen($this->code) && $token != "(" && $token != ")" && $token != "!" && $token != "{" && $token != "}" && $token != "#" && $token != "*" && $token != "/") {
                                        $this->jndtoks[] = $token;

                                        if($pos < strlen($this->code)) {
                                                $token = $this->code[$pos];
                                        }
                                        $pos++;
                                }
                                $this->tokens[] = array($this->t_identifier, join("", $this->jndtoks));
                                $this->jndtoks = array();
                                $pos--;
                        }
                }
                return $this->tokens;
        }
}


class Jang {
        use TokenType;
        public $variables = array();
        public $rets = array();
        public $functions = array();

        public function __construct() {

        }

        public function Execute($code) {
                $lex = new Lexer($code);
                $tokens = $lex->Tokenize();
                $pos = 0;
                while ($pos < sizeof($tokens)) {
                        $token = $tokens[$pos];
                        $pos++;

                        if ($token[0] == $this->t_int) {
                                $this->rets[] = $token[1];
                        } else if($token[0] == $this->t_float) {
                                $this->rets[] = $token[1];
                        } else if($token[0] == $this->t_string) {
                                $this->rets[] = str_replace("\\n", "\n", $token[1]);
                        } else if($token[0] == $this->t_plus) {
                                $token = $tokens[$pos];
                                $pos++;
                                if($token[0] == $this->t_var) {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $this->rets[] = array_pop($this->rets) + $this->variables[$token[1]];
                                } else {
                                        $this->rets[] = array_pop($this->rets) + $token[1];
                                }
                        } else if($token[0] == $this->t_minus) {
                                $token = $tokens[$pos];
                                $pos++;
                                if($token[0] == $this->t_var) {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $this->rets[] = array_pop($this->rets) - $this->variables[$token[1]];
                                } else {
                                        $this->rets[] = array_pop($this->rets) - $token[1];
                                }
                        } else if($token[0] == $this->t_times) {
                                $token = $tokens[$pos];
                                $pos++;
                                if($token[0] == $this->t_var) {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $this->rets[] = array_pop($this->rets) * $this->variables[$token[1]];
                                } else {
                                        $this->rets[] = array_pop($this->rets) * $token[1];
                                }
                        } else if($token[0] == $this->t_divide) {
                                $token = $tokens[$pos];
                                $pos++;
                                if($token[0] == $this->t_var) {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $this->rets[] = array_pop($this->rets) / $this->variables[$token[1]];
                                } else {
                                        $this->rets[] = array_pop($this->rets) / $token[1];
                                }
                        } else if($token[0] == $this->t_identifier) {
                                if ($token[1] == "println") {
                                        $code = "";
                                        $token = $tokens[$pos];
                                        $pos++;
                                        while($token[0] != $this->t_semicolon && $pos <= sizeof($tokens)) {
                                                if($token[0] == $this->t_string) {
                                                        $code = $code . "\"" . $token[1] . "\" ";
                                                } else {
                                                        $code = $code . $token[1] . " ";
                                                }

                                                if($pos < sizeof($tokens)) {
                                                        $token = $tokens[$pos];
                                                }
                                                $pos++;
                                        }
                                        $this->Execute($code);
                                        foreach($this->rets as $ret) {
                                                echo $ret;
                                        }

                                        for($i = 0; $i <= sizeof($this->rets); $i++) {
                                                array_pop($this->rets);
                                        }
                                } else if($token[1] == "function") {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $name = $token[1];
                                        $params = array();
                                        $endnum = 0;
                                        $token = $tokens[$pos];
                                        $pos++;
                                        while($token[0] != $this->t_lbracket && $pos <= sizeof($tokens)) {
                                                $params[] = $token[1];

                                                if($pos < sizeof($tokens)) {
                                                        $token = $tokens[$pos];
                                                }
                                                $pos++;
                                        }
                                        /*
                                        functions:
                                                function sum n1 n2 {
                                                        #n1 + #n2
                                                }
                                         */
                                        $endnum++;
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $this->functions[$name] = array("params" => $params, "code" => array());
                                        while($endnum > 0 && $pos < sizeof($tokens)) {
                                                if($token[0] == $this->t_lbracket) {
                                                        $endnum++;
                                                        $this->functions[$name]["code"][] = $token[1];
                                                } else if($token[0] == $this->t_rbracket) {
                                                        $endnum--;
                                                        if($endnum >= 1) {
                                                                $this->functions[$name]["code"][] = $token[1];
                                                        }
                                                } else {
                                                        $this->functions[$name]["code"][] = $token[1];
                                                }
                                                $token = $tokens[$pos];
                                                $pos++;
                                        }
                                        $pos--;
                                } else if($token[1] == "var") {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $name = $token[1];
                                        $token = $tokens[$pos];
                                        $pos++;
                                        if($token[0] == $this->t_set) {
                                                $code = "";
                                                $token = $tokens[$pos];
                                                $pos++;
                                                while($token[0] != $this->t_semicolon && $pos <= sizeof($tokens)) {
                                                        if($token[0] == $this->t_string) {
                                                                $code = $code . "\"" . $token[1] . "\" ";
                                                        } else {
                                                                $code = $code . $token[1] . " ";
                                                        }

                                                        if($pos < sizeof($tokens)) {
                                                                $token = $tokens[$pos];
                                                        }
                                                        $pos++;
                                                }
                                                $this->Execute($code);
                                                $this->variables[$name] = array_pop($this->rets);
                                        } else {
                                                die("Error: use ;= to set variables values!");
                                        }
                                } else if($token[1] == "clearets") {
                                        $this->rets = array();
                                } else if($token[1] == "list") {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $this->variables[$token[1]] = array();
                                } else if($token[1] == "append") {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        $name = $token[1];
                                        $token = $tokens[$pos];
                                        $pos++;
                                        if($token[0] == $this->t_lparen) {
                                                $code = "";
                                                $token = $tokens[$pos];
                                                $pos++;
                                                while($token[0] != $this->t_rparen && $pos <= sizeof($tokens)) {
                                                        if($token[0] == $this->t_string) {
                                                                $code = $code . "\"" . $token[1] . "\" ";
                                                        } else {
                                                                $code = $code . $token[1] . " ";
                                                        }

                                                        if($pos < sizeof($tokens)) {
                                                                $token = $tokens[$pos];
                                                        }
                                                        $pos++;
                                                }
                                                $this->Execute($code);
                                                $this->variables[$name][] = array_pop($this->rets);
                                        }
                                } else if($token[1] == "pop") {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        array_pop($this->variables[$token[1]]);
                                } else if($token[1] == "ar_print") {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        echo "[\n";
                                        foreach($this->variables[$token[1]] as $item) {
                                                if(gettype($item) == "array") {
                                                        print_r($item);
                                                } else {
                                                        echo "  " . $item . ",\n";
                                                }
                                        }
                                        echo "]\n";
                                }
                        } else if($token[0] == $this->t_var) {
                                $token = $tokens[$pos];
                                $pos++;
                                $name = $token[1];
                                if($pos < sizeof($tokens)) {
                                        $token = $tokens[$pos];
                                        $pos++;
                                        if($token[0] == $this->t_lparen) {
                                                $code = "";
                                                $token = $tokens[$pos];
                                                $pos++;
                                                while($token[0] != $this->t_rparen && $pos <= sizeof($tokens)) {
                                                        if($token[0] == $this->t_string) {
                                                                $code = $code . "\"" . $token[1] . "\" ";
                                                        } else {
                                                                $code = $code . $token[1] . " ";
                                                        }

                                                        if($pos < sizeof($tokens)) {
                                                                $token = $tokens[$pos];
                                                        }
                                                        $pos++;
                                                }
                                                $this->Execute($code);
                                                $this->rets[] = $this->variables[$name][array_pop($this->rets)];
                                        } else {
                                                $pos--;
                                                $this->rets[] = $this->variables[$name];
                                        }
                                } else {
                                        $this->rets[] = $this->variables[$name];
                                }
                        } else if($token[0] == $this->t_call) {
                                $token = $tokens[$pos];
                                $pos++;
                                $params = array();
                                $name = $token[1];
                                $code = "";
                                $token = $tokens[$pos];
                                $pos++;
                                if($token[0] == $this->t_lparen) {
                                        $argsendnum = 0;
                                        $argsendnum++;
                                        while($argsendnum > 0 && $pos <= sizeof($tokens)) {
                                                if($token[0] == $this->t_rparen) {
                                                        $argsendnum--;
                                                        if($argsendnum >= 1) {
                                                                if($token[0] == $this->t_string) {
                                                                        $code = $code . "\"" . $token[1] . "\" ";
                                                                } else {
                                                                        $code = $code . $token[1] . " ";
                                                                }
                                                        }
                                                } else if($token[0] == $this->t_lparen) {
                                                        $argsendnum++;
                                                        if($token[0] == $this->t_string) {
                                                                $code = $code . "\"" . $token[1] . "\" ";
                                                        } else {
                                                                $code = $code . $token[1] . " ";
                                                        }
                                                } else {
                                                        if($token[0] == $this->t_string) {
                                                                $code = $code . "\"" . $token[1] . "\" ";
                                                        } else {
                                                                $code = $code . $token[1] . " ";
                                                        }
                                                }

                                                if($pos < sizeof($tokens)) {
                                                        $token = $tokens[$pos];
                                                }
                                                $pos++;
                                        }
                                        $this->rets = array();
                                        $this->Execute($code);
                                        foreach($this->rets as $ret) {
                                                $params[] = $ret;
                                        }
                                        $this->rets = array();
                                        if(sizeof($params) <= sizeof($this->functions[$name]["params"])) {
                                                for($i = 0; $i < sizeof($params); $i++) {
                                                        $this->variables[$this->functions[$name]["params"][$i]] = $params[$i];
                                                }
                                        } else {
                                                die("Error: too many arguments!\n");
                                        }
                                        $fcode = join(" ", $this->functions[$name]["code"]);
                                        $this->Execute($fcode);
                                } else {
                                        die("Error: use '(' to start functions arguments (and call it)");
                                }
                        }
                }
        }
}

$jang = new Jang();
if($argc < 2) {
        die("Usage: php $argv[0] <file.ja>\n");
} else {
        if(str_ends_with($argv[1], ".ja")) {
                $filee = fopen($argv[1], "r") or die("Error: can't open file: $argv[1]\n");
                $filesize = filesize($argv[1]);
                $content = fread($filee, $filesize);
                $lang = new Jang();
                $lang->Execute($content);
        } else {
                die("Error: use '.ja' file extension!");
        }
}
?>
