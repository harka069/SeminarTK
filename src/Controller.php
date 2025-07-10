<?php
class Controller
{
   public function methodNotAllowed(string $allowed_method): void
    {
        http_response_code(405);
        header("Allow: $allowed_method");
    }
    public function NotEnoughParameters(): void
    {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Not enough parameters"]);
    }
    public function clean_string_input(string|null $input): string|null 
    {
    return $input !== null ? strip_tags($input) : null;
    }
}