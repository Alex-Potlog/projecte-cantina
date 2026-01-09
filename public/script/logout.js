/**
 * Funció que espera a que tot l'HTML estigui carregat i
 * prerarat per executar la funció que crida.
 */
window.addEventListener("DOMContentLoaded", function () {
    updateUI();
})

/**
 * Funció que comprova si l'usuari està logejat o no.
 * @returns retorna true o false depenent de si està logejat o no.
 */
async function isLoggedIn() {
    try {
        const response = await fetch("../php/isLoggedIn.php", { method: "POST" });
        const data = await response.json();
        console.log("Respuesta del servidor:", data);
        return data.correcte; // true o false
    } catch (error) {
        console.error("Error:", error);
        return false;
    }
}

/**
 * Funció que ens actualitza el botó d'iniciar/tancar sessió
 * basat en l'estat del login
 */
async function updateUI() {
    const loggedIn = await isLoggedIn();

    if (loggedIn) {
        console.log("dins del loggedIn");
        const loginForm = document.querySelector("#loginForm");

        document.querySelector(".envia").style.display = "none";

        const logoutBoto = document.createElement('button');
        logoutBoto.type = 'submit';
        logoutBoto.className = 'logout envia';
        logoutBoto.id = 'logout';
        logoutBoto.innerHTML = 'Tancar sessió';

        logoutBoto.onclick = logout;

        loginForm.insertAdjacentElement("beforeend", logoutBoto);
        // User is logged in, so show the logout button container
        /* botoLogin.textContent = "Tanca la sessió"*/

    } else {
        console.log("No es logged in");
    }
}

/**
 * Funció que envia al servidor la petició de logout i després recarrega 
 * la pàgina per actualitzar l'estat de la sessió.
 * 
 * @returns Retorna 'void' (no res) si la petició té èxit o false si hi ha un error en la petició
 */
async function logout() {
    try {
        const response = await fetch("../php/logout.php", { method: "POST" });
        //const data = await response.json(); // aquí esperas la respuesta en JSON
        window.location.reload();
    } catch (error) {
        console.error("Error:", error);
        return false;
    }
}
