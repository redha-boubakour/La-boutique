window.onload = () => {
    const FiltersForm = document.querySelector("#filters");

    // On boucle sur les input
    document.querySelectorAll("#filters input").forEach(input => {

        // On intercepte les clics
        input.addEventListener("change", () => {
            // On récupère les données du formulaire
            const Form = new FormData(FiltersForm);

            // la "query string" (tout ce qui est après le point d'interrogation dans l'URL)
            const Params = new URLSearchParams();

            Form.forEach((value, key) => {
                Params.append(key, value)
            })

            // On récupère l'URL active
            const Url = new URL(window.location.href);

            // La requete AJAX
            fetch(Url.pathname + "?" + Params.toString() + "&ajax=1", {
                headers: {
                    "X-requested-with": "XMLHttpRequest"
                }
            }).then(Response => 
                Response.json()
            ).then(data =>{
                // Chercher la zone de contenu
                const content = document.querySelector("#content")
                
                //on remplace le contenu
                content.innerHTML = data.content;
            }).catch(e => alert(e));

        });
    });
}