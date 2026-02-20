// Fonction pour changer l'ordre des articles
function orderPosts() {
  const list = document.getElementById("postslist-container");
  const order = document.querySelector("#order").value;

  // Construire l'URL pour trier tous les articles
  const url = `/order/${order}`;

  // Envoyer la requête AJAX
  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: `order=${encodeURIComponent(order)}`,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }
      return response.text();
    })
    .then((html) => {
      list.innerHTML = html;
    })
    .catch((error) => {
      console.error("Erreur lors de la récupération des posts:", error);
    });
}

// Fonction pour aller à un post spécifique
function goToPost(id) {
  const list = document.getElementById("postslist-container");
  const path = window.location.pathname;
  const slug = path.substring(path.lastIndexOf("/") + 1);
  console.log("/" + slug + "/" + id);
  let xhr = new XMLHttpRequest();
  xhr.open("GET", "/" + slug + "/" + id);
  xhr.onload = function () {
    if (xhr.status === 200) {
      list.innerHTML = xhr.responseText;
      console.log("ok", xhr.responseText);
    } else {
      console.log(
        "Erreur lors de la récupération des événements : " + xhr.status,
      );
    }
  };
  xhr.send();
}

// Gestion des focus/blur/keyup pour les inputs (informations contact)
const inputs = document.querySelectorAll("#form-contact .form-control");
console.log(inputs);
inputs.forEach(function (input) {
  input.addEventListener("focusin", function (e) {
    console.log(e.target.value);
    if (e.target.value.length != 0) {
      e.target.closest(".form-group").classList.add("show-label");
    } else {
      e.target.closest(".form-group").classList.remove("show-label");
    }
  });
});
inputs.forEach(function (input) {
  input.addEventListener("focusout", function (e) {
    console.log(e.target.value);
    if (e.target.value.length != 0) {
      e.target.closest(".form-group").classList.add("show-label");
    } else {
      e.target.closest(".form-group").classList.remove("show-label");
    }
  });
});
inputs.forEach(function (input) {
  input.addEventListener("keyup", function (e) {
    console.log(e.target.value);
    if (e.target.value.length != 0) {
      e.target.closest(".form-group").classList.add("show-label");
    } else {
      e.target.closest(".form-group").classList.remove("show-label");
    }
  });
});
