const html = document.documentElement;

const btn = document.getElementById("btnTheme");
const label = document.getElementById("themeLabel");

const modes = ["light", "dark", "auto"];

let current = localStorage.getItem("theme") || "auto";

function applyTheme(mode) {
    // Aplica tema
    if (mode === "auto") {
        const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        html.setAttribute("data-bs-theme", prefersDark ? "dark" : "light");
    } else {
        html.setAttribute("data-bs-theme", mode);
    }

    // Atualiza texto do botão
    label.textContent = "Tema: " + mode.charAt(0).toUpperCase() + mode.slice(1);
}

// Aplicar tema inicial
applyTheme(current);

// Alternância ciclica
btn.addEventListener("click", () => {
    let index = modes.indexOf(current);
    current = modes[(index + 1) % modes.length]; // próximo
    localStorage.setItem("theme", current);
    applyTheme(current);
});

// Detecta mudança no sistema (modo AUTO)
window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
    if (current === "auto") {
        applyTheme("auto");
    }
});
