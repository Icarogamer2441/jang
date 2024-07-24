<?php

function readFileInFolder($fileName) {
    $folderName = 'jangimps';
    
    function getFileContent($directory, $fileName, $folderName) {
        $folderPath = $directory . '/' . $folderName;
        
        if (is_dir($folderPath)) {
            $filePath = $folderPath . '/' . $fileName;
            
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            } else {
                return "File '$fileName' not found in folder '$folderName'.";
            }
        }
        return null;
    }
    
    $content = getFileContent('.', $fileName, $folderName);
    
    if ($content === null) {
        $content = getFileContent('..', $fileName, $folderName);
        
        if ($content === null) {
            return "Folder '$folderName' not found in any of the directories searched.";
        }
    }
    
    return $content;
}

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
	public $t_equal = "EQUAL";
	public $t_notequal = "NOTEQUAL";
	public $t_greater = "GREATERTHAN";
	public $t_less = "LESSTHAN";
	public $t_ge = "GREATEREQUAL";
	public $t_le = "LESSEQUAL";
	public $t_comment = "COMMENT";
	public $t_structpoint = "STRUCTPOINT";
	public $t_structget = "STRUCTGET";
	public $t_conc = "CONCATENATE";
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
				if ($pos < strlen($this->code)) {
					if($this->code[$pos] == ">") {
						$this->tokens[] = array($this->t_structpoint, "->");
						$pos++;
					} else {
						$this->tokens[] = array($this->t_minus, "-");
					}
				} else {
					$this->tokens[] = array($this->t_minus, "-");
				}
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
				if($pos < strlen($this->code)) {
					if($this->code[$pos] == "=") {
						$this->tokens[] = array($this->t_notequal, "!=");
						$pos++;
					} else {
						$this->tokens[] = array($this->t_call, "!");
					}
				} else {
					$this->tokens[] = array($this->t_call, "!");
				}
			} else if($token == "{") {
				$this->tokens[] = array($this->t_lbracket, "{");
			} else if($token == "}") {
				$this->tokens[] = array($this->t_rbracket, "}");
			} else if($token == "#") {
				$this->tokens[] = array($this->t_var, "#");
			} else if($token == "=") {
				$this->tokens[] = array($this->t_equal, "=");
			} else if($token == ">") {
				if($pos < strlen($this->code)) {
					if($this->code[$pos] == "=") {
						$this->tokens[] = array($this->t_ge, ">=");
						$pos++;
					} else {
						$this->tokens[] = array($this->t_greater, ">");
					}
				} else {
					$this->tokens[] = array($this->t_greater, ">");
				}
			} else if($token == "<") {
				if($pos < strlen($this->code)) {
					if($this->code[$pos] == "=") {
						$this->tokens[] = array($this->t_le, "<=");
						$pos++;
					} else {
						$this->tokens[] = array($this->t_less, "<");
					}
				} else {
					$this->tokens[] = array($this->t_less, "<");
				}
			} else if($token == "@") {
				$this->tokens[] = array($this->t_comment, "@");
			} else if($token == "$") {
				$this->tokens[] = array($this->t_structget, "$");
			} else if($token == ":") {
				$this->tokens[] = array($this->t_conc, ":");
			} else {
				while($token != " " && $token != "\n" && $token != "\t" && $token != "+" && $token != "\"" && $token != ";" && $token != "-" && $pos <= strlen($this->code) && $token != "(" && $token != ")" && $token != "!" && $token != "{" && $token != "}" && $token != "#" && $token != "*" && $token != "/" && $token != "=" && $token != ">" && $token != "<" && $token != "$" && $token != "@" && $token != ":") {
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
	public $structs = array();
	public $svars = array();
	public $whiles = array(false);
	public $whilepos = -1;

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
				$this->rets[] = str_replace("\\t", "\t", str_replace("\\n", "\n", $token[1]);
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

					$this->rets = array();
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
							if($token[0] == $this->t_string) {
								$this->functions[$name]["code"][] = "\"" . $token[1] ."\"";
							} else {
								$this->functions[$name]["code"][] = $token[1];
							}
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
				} else if($token[1] == "if") {
					$token = $tokens[$pos];
					$pos++;
					$endnum = 0;
					if($token[0] == $this->t_lparen) {
						$code = "";
						$token = $tokens[$pos];
						$pos++;
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
						$this->Execute($code);
						$istrue = array_pop($this->rets);
						$endnum++;
						$token = $tokens[$pos];
						$pos++;
						$ifcode = "";
						while($endnum > 0 && $pos < sizeof($tokens)) {
							if($token[0] == $this->t_lbracket) {
								$endnum++;
								$ifcode = $ifcode . $token[1] . " ";
							} else if($token[0] == $this->t_rbracket) {
								$endnum--;
								if($endnum >= 1) {
									$ifcode = $ifcode . $token[1] . " ";
								}
							} else {
								if($token[0] == $this->t_string) {
									$ifcode = $ifcode . "\"" . $token[1] . "\" ";
								} else {
									$ifcode = $ifcode . $token[1] . " ";
								}
							}
							$token = $tokens[$pos];
							$pos++;
						}
						$pos--;
						if($istrue) {
							$this->Execute($ifcode);
						}
					} else {
						die("Error: Use '(' to start if checks!\n");
					}
				} else if($token[1] == "toint") {
					$code = "";
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_lparen) {
						$token = $tokens[$pos];
						$pos++;
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
						$this->Execute($code);
						$this->rets[] = (int)array_pop($this->rets);
						$pos--;
					} else {
						die("Error: use '(' to start the arguments!\n");
					}
				} else if($token[1] == "tofloat") {
					$code = "";
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_lparen) {
						$token = $tokens[$pos];
						$pos++;
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
						$this->Execute($code);
						$this->rets[] = (float)array_pop($this->rets);
						$pos--;
					} else {
						die("Error: use '(' to start the arguments!\n");
					}
				} else if($token[1] == "tostring") {
					$code = "";
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_lparen) {
						$token = $tokens[$pos];
						$pos++;
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
						$this->Execute($code);
						$this->rets[] = (string)array_pop($this->rets);
						$pos--;
					} else {
						die("Error: use '(' to start the arguments!\n");
					}
				} else if($token[1] == "struct") {
					$token = $tokens[$pos];
					$pos++;
					$name = $token[1];
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_lbracket) {
						$token = $tokens[$pos];
						$pos++;
						$items = array();
						while($token[0] != $this->t_rbracket && $pos <= sizeof($tokens)) {
							$items[] = $token[1];

							if($pos < sizeof($tokens)) {
								$token = $tokens[$pos];
							}
							$pos++;
						}
						$this->structs[$name] = $items;
					} else {
						die("Error: use '{' to start set the struct arguments\n");
					}
				} else if($token[1] == "svar") {
					$token = $tokens[$pos];
					$pos++;
					$name = $token[1];
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_set) {
						$token = $tokens[$pos];
						$pos++;
						$this->svars[$name] = $this->structs[$token[1]];
					} else {
						die("Error: use ';=' to set struct variables\n");
					}
				} else if($token[1] == "svset") {
					$token = $tokens[$pos];
					$pos++;
					$name = $token[1];
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_structpoint) {
						$token = $tokens[$pos];
						$pos++;
						$part = $token[1];
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
							$this->svars[$name][$part] = array_pop($this->rets);
						} else {
							die("Error: use ';=' to set struct variables arguments values\n");
						}
					} else {
						die("Error: use '->' to reference the argument name");
					}
				} else if($token[1] == "type") {
					$code = "";
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_lparen) {
						$token = $tokens[$pos];
						$pos++;
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
						$pos--;
						$this->Execute($code);
						$this->rets[] = gettype(array_pop($this->rets));
					} else {
						die("Error: use '(' to start the arguments!\n");
					}
				} else if($token[1] == "break") {
					$this->whiles[$this->whilepos] = false;
					$this->whilepos--;
				} else if($token[1] == "while") {
					$token = $tokens[$pos];
					$pos++;
					$this->whilepos++;
					$endnum = 0;
					while($this->whilepos < 0 || $this->whilepos >= sizeof($this->whiles)) {
						if($this->whilepos >= sizeof($this->whiles)) {
							$this->whiles[] = true;
						} else if ($this->whilepos < 0) {
							$this->whilepos++;
						}
					}

					$this->whiles[$this->whilepos] = true;

					$whilepos = $this->whilepos;
					if($token[0] == $this->t_lbracket) {
						$endnum++;
						$token = $tokens[$pos];
						$pos++;
						$whcode = "";
						while($endnum > 0 && $pos < sizeof($tokens)) {
							if($token[0] == $this->t_lbracket) {
								$endnum++;
								$whcode = $whcode . $token[1] . " ";
							} else if($token[0] == $this->t_rbracket) {
								$endnum--;
								if($endnum >= 1) {
									$whcode = $whcode . $token[1] . " ";
								}
							} else {
								if($token[0] == $this->t_string) {
									$whcode = $whcode . "\"" . $token[1] . "\" ";
								} else {
									$whcode = $whcode . $token[1] . " ";
								}
							}
							$token = $tokens[$pos];
							$pos++;
						}
						$pos--;

						while($this->whiles[$whilepos]) {
							$this->Execute($whcode);
						}
					} else if($token[0] == $this->t_lparen) {
						die("Error: whiles can't do '=|!=', '>|<' or '>=|<=' in this language!\n");
					} else {
						die("Error: use '{' to start while code!\n");
					}
				} else if($token[1] == "array") {
					$this->rets[] = array();
				} else if($token[1] == "import") {
					$token = $tokens[$pos];
					$pos++;
					if(str_ends_with($token[1], ".ja")) {
						$content = readFileInFolder($token[1]);
						$this->Execute($content);
					} else {
						die("Error: use '.ja' file extension!");
					}
				} else if($token[1] == "sizeof") {
					$code = "";
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_lparen) {
						$token = $tokens[$pos];
						$pos++;
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
						$pos--;
						$this->Execute($code);
						$this->rets[] = sizeof(array_pop($this->rets));
					} else {
						die("Error: use '(' to start the arguments!\n");
					}
				} else if($token[1] == "len") {
					$code = "";
					$token = $tokens[$pos];
					$pos++;
					if($token[0] == $this->t_lparen) {
						$token = $tokens[$pos];
						$pos++;
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
						$pos--;
						$this->Execute($code);
						$this->rets[] = strlen(array_pop($this->rets));
					} else {
						die("Error: use '(' to start the arguments!\n");
					}
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
			} else if($token[0] == $this->t_equal) {
				$token = $tokens[$pos];
				$pos++;
				if($token[0] == $this->t_var) {
					$token = $tokens[$pos];
					$pos++;
					$this->rets[] = array_pop($this->rets) === $this->variables[$token[1]];
				} else {
					$this->rets[] = array_pop($this->rets) === $token[1];
				}
			} else if($token[0] == $this->t_notequal) {
				$token = $tokens[$pos];
				$pos++;
				if($token[0] == $this->t_var) {
					$token = $tokens[$pos];
					$pos++;
					if(array_pop($this->rets) !== $this->variables[$token[1]]) {
						$this->rets[] = 1;
					} else {
						$this->rets[] = 0;
					}
				} else {
					$this->rets[] = array_pop($this->rets) !== $token[1];
				}
			} else if($token[0] == $this->t_greater) {
				$token = $tokens[$pos];
				$pos++;
				if($token[0] == $this->t_var) {
					$token = $tokens[$pos];
					$pos++;
					$this->rets[] = array_pop($this->rets) > $this->variables[$token[1]];
				} else {
					$this->rets[] = array_pop($this->rets) > $token[1];
				}
			} else if($token[0] == $this->t_less) {
				$token = $tokens[$pos];
				$pos++;
				if($token[0] == $this->t_var) {
					$token = $tokens[$pos];
					$pos++;
					$this->rets[] = array_pop($this->rets) < $this->variables[$token[1]];
				} else {
					$this->rets[] = array_pop($this->rets) < $token[1];
				}
			} else if($token[0] == $this->t_ge) {
				$token = $tokens[$pos];
				$pos++;
				if($token[0] == $this->t_var) {
					$token = $tokens[$pos];
					$pos++;
					$this->rets[] = array_pop($this->rets) >= $this->variables[$token[1]];
				} else {
					$this->rets[] = array_pop($this->rets) >= $token[1];
				}
			} else if($token[0] == $this->t_le) {
				$token = $tokens[$pos];
				$pos++;
				if($token[0] == $this->t_var) {
					$token = $tokens[$pos];
					$pos++;
					$this->rets[] = array_pop($this->rets) <= $this->variables[$token[1]];
				} else {
					$this->rets[] = array_pop($this->rets) <= $token[1];
				}
			} else if($token[0] == $this->t_comment) {
				if($pos < sizeof($tokens)) {
					$token = $tokens[$pos];
					$pos++;
					while($token[0] != $this->t_comment && $pos <= sizeof($tokens)) {
						if($pos < sizeof($tokens)) {
							$token = $tokens[$pos];
						}
						$pos++;
					}
				}
			} else if($token[0] == $this->t_structget) {
				$token = $tokens[$pos];
				$pos++;
				$name = $token[1];
				$token = $tokens[$pos];
				$pos++;
				if ($token[0] == $this->t_structpoint) {
					$token = $tokens[$pos];
					$pos++;
					$this->rets[] = $this->svars[$name][$token[1]];
				}
			} else if($token[0] == $this->t_conc) {
				$this->rets[] = array_pop($this->rets) . array_pop($this->rets);
			}
		}
	}
}

$jang = new Jang();
$version = "0.4";
if($argc < 2) {
	echo "Jang version: $version\n";
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
