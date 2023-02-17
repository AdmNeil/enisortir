"use strict";

const V_n = document.getElementById('ville_nom');
const L_r = document.getElementById('lieu_rue');

if(L_r !== undefined && V_n !== undefined) {
    let div = document.createElement('div');

    div.setAttribute(`class`, `open`);

    L_r.addEventListener(`keyup`, e => {
        let child = div.lastElementChild;

        while (child) {
            div.removeChild(child);
            child = div.lastElementChild;
        }

        fetch(`https://api-adresse.data.gouv.fr/search/?q=${e.target.value} ${V_n.value}&type=street&autocomplete=0`, {
            methode: 'GET',
            mode: 'cors'
        }).then(resp => resp.json())
        .then(body => {
            let adresseList = document.getElementsByClassName('open')[0];

            if(adresseList.classList.contains('listCacher')) {
                adresseList.classList.remove('listCacher');
            }

            for (const adresse of body.features) {
                let objectAdresse = document.createElement('div');
                let objectAdresseLabelle = document.createElement('p');

                objectAdresseLabelle.textContent = adresse.properties.label;

                objectAdresseLabelle.addEventListener(`click`, () => {
                    V_n.value = adresse.properties.city;
                    L_r.value = adresse.properties.street;
                    document.getElementById('ville_codePostal').value = adresse.properties.postcode;
                    document.getElementById('lieu_latitude').value = adresse.geometry.coordinates[1];
                    document.getElementById('lieu_longitude').value = adresse.geometry.coordinates[0];
                    adresseList.classList.add('listCacher');
                });

                objectAdresse.appendChild(objectAdresseLabelle);
                div.appendChild(objectAdresse);
            }
        }).catch(error => console.error(error));
    });

    L_r.parentElement.appendChild(div);
}