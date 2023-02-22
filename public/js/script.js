"use strict";

class Script {
    constructor() {
        this.getTitle = location.pathname;
        this.lastIndex = this.getTitle.lastIndexOf('/');
        this.pathName = this.getTitle.match(/(?<=\/)[a-zA-Z]+(?=\/)?/g);
        this.getPathName = this.pathName === null ? "home" : this.pathName[0];
        this.is_function(this.getPathName);
        this.popup();
    }

    is_function(path) {
        if({}.toString.call(this[path]) === '[object Function]') this[path]();
    }

    popup() {
        let listPopup = document.getElementsByClassName(`message`)[0].querySelectorAll(`.un-popup`);

        listPopup.forEach(el => {
            el.addEventListener(`click`, () => {
                el.remove();
            })
        })
    }

    home() {
        document.getElementById(`filtreSubmit`).addEventListener(`click`, () => {
            let errSite = document.getElementById('errorSite');
            let errNom = document.getElementById('errorNom');
            let errDate = document.getElementById('errorDate');

            errSite.textContent = "";
            errNom.textContent = "";
            errDate.textContent = "";

            const formData = new FormData();

            formData.append("filtre", "");
            formData.append('filtre_home_site', document.getElementById(`filtre_home_site`).value);
            formData.append('filtreNom', document.getElementById(`filtreNom`).value);
            formData.append('filtreDateMin', document.getElementById(`filtreDateMin`).value);
            formData.append('filtreDateMax', document.getElementById(`filtreDateMax`).value);
            formData.append('cocheOrganisateur', document.getElementById(`cocheOrganisateur`).checked);
            formData.append('cocheInscrit', document.getElementById(`cocheInscrit`).checked);
            formData.append('cocheNonInscrit', document.getElementById(`cocheNonInscrit`).checked);
            formData.append('cochePassees', document.getElementById(`cochePassees`).checked);

            fetch("./filtre", {
                method: "POST",
                mode: "cors",
                credentials: "same-origin",
                body: formData
            }).then(response => response.json()
            ).then(body => {
                if(body.error !== undefined) {
                    if(body.error.site !== undefined) {
                        errSite.textContent = body.error.site;
                    }

                    if(body.error.nom !== undefined) {
                        errNom.textContent = body.error.nom;
                    }

                    if(body.error.date !== undefined) {
                        errDate.textContent = body.error.date;
                    }

                    return;
                }

                if ('content' in document.createElement('template')) {
                    let listSortie = document.getElementsByClassName(`main-list-filter`)[0];

                    this.removeAll(listSortie);

                    for (let objr of body) {
                        let obj = JSON.parse(objr);
                        let prtcp = obj[0];
                        let cmplPrtcp = obj[1];
                        const template = document.getElementsByTagName(`template`)[0];
                        const clone = template.content.cloneNode(true);
                        const custDate = new Date(prtcp.dateHeureDeb);

                        clone.querySelector(`[tpl="nom"]`).textContent = prtcp.nom;
                        clone.querySelector(`[tpl="debut"]`).textContent = custDate.toLocaleDateString() + " à " + custDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        clone.querySelector(`[tpl="cloture"]`).textContent = new Date(prtcp.dateCloture).toLocaleDateString();
                        clone.querySelector(`[tpl="inscpla"]`).textContent = `${cmplPrtcp.countParticipant} / ${prtcp.nbInscriptionsMax}`;
                        clone.querySelector(`[tpl="etat"]`).textContent = prtcp.etat.libelle;
                        clone.querySelector(`[tpl="organisateur"]`).textContent = `${prtcp.organisateur.prenom} ${prtcp.organisateur.nom}`;
                        clone.querySelector(`[tpl="organisateur"]`).href = `/profile/show/${prtcp.organisateur.id}`;
                        clone.querySelector(`[tpl="inscrit"]`).textContent =  cmplPrtcp.isInscrit === 0 ? `Non` : `Oui`;
                        let action = clone.querySelector(`[tpl="action"]`);

                        for (const [i, uneAction] of Object.entries(cmplPrtcp.action)) {
                            let a = document.createElement(`a`);

                            a.href = uneAction.path + prtcp.id;
                            a.textContent = uneAction.name;

                            if(i === cmplPrtcp.action.length -1 && cmplPrtcp.action.length -1) {
                                let span = document.createElement(`span`);

                                span.textContent = " / ";

                                action.appendChild(span);
                            }

                            action.appendChild(a);
                        }

                        listSortie.appendChild(clone);
                    }

                } else {
                    console.error("Vote navigateur ne peux pas gérer la balise template");
                }

            })//.catch(e => console.error(`Problème de réseau ou Parse: ${e}`));
        })
    }

    removeAll(container) {
        let child = container.lastElementChild;

        while (child) {
            container.removeChild(child);
            child = container.lastElementChild;
        }
    }

    sortie() {
        const V_n = document.getElementById('ville_nom');
        const L_r = document.getElementById('lieu_rue');
        let div = document.createElement('div');

        div.setAttribute(`class`, `open`);

        L_r.addEventListener(`keyup`, e => {
            this.removeAll(div);

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
}

(() => {
    const script = new Script();

    if(!(script instanceof Object)) {
        console.error(`Le Script n'est pas un Objet`);
        void 0;
    } else {
        window.addEventListener("load", () => {}, {once: true});
        /*let data=window.performance.getEntriesByType("navigation")[0].type;

        if(data === "reload") {
            window.addEventListener("load", () => {
                document.getElementById('load').style.opacity = 1;
            }, {once: true});
        } else {
            window.addEventListener("load", () => {document.getElementById('load').setAttribute('id', '')}, {once: true});
        }*/
    }
})()

void 0;