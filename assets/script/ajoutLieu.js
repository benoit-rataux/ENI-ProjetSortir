let ajouterLieu = document.getElementById("ajouterLieu");
let lieu = document.getElementById("lieu");

ajouterLieu.addEventListener("click", () => {
    if(getComputedStyle(lieu).display != "none"){
        lieu.style.display = "none";
    }
    else{
        lieu.style.display = "block";
    }
});