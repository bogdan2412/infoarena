<?php

abstract class EvalException extends Exception {
    abstract public function getShortMessage();
}

final class EvalSystemError extends EvalException {
    public function __construct($message, $code = 0,
                                Exception $previous = null) {
        parent::__construct("Contactează un administrator\n" . $message,
                            $code, $previous);
    }

    public function getShortMessage() {
        return 'Eroare de sistem';
    }
}

final class EvalTaskOwnerError extends EvalException {
    public function __construct($message, $code = 0,
                                Exception $previous = null) {
        parent::__construct("Contactează autorul problemei:\n" . $message,
                            $code, $previous);
    }

    public function getShortMessage() {
        return 'Eroare în configurarea problemei';
    }
}

abstract class EvalUserError extends EvalException {
}

final class EvalUserCompileError extends EvalUserError {
    public function __construct($message, $code = 0,
                                Exception $previous = null) {
        parent::__construct("Eroare de compilare:\n" . $message,
                            $code, $previous);
    }

    public function getShortMessage() {
        return 'Eroare de compilare';
    }
}
