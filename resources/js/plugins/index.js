import iro from "@jaames/iro"
import jquery from "jquery"

try {
    window.iro = iro
    window.$ = window.jQuery = jquery
} catch (e) {}
