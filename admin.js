// ----- MACHINERY SECTION -----
const machineryForm = document.getElementById("machineryForm");
const machineryList = document.getElementById("machineryList");

machineryForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const inputs = machineryForm.querySelectorAll("input, textarea, select");

  const machinery = {
    name: inputs[0].value,
    image: inputs[1].value,
    description: inputs[2].value,
    availability: inputs[3].value,
    price: inputs[4].value,
    rating: inputs[5].value,
  };

  addMachineryCard(machinery);
  machineryForm.reset();
});

function addMachineryCard(data) {
  const card = document.createElement("div");
  card.classList.add("entry-box");
  card.innerHTML = `
    <h3>${data.name}</h3>
    <img src="${data.image}" alt="${data.name}" style="width:100%; height:150px; object-fit:cover; border-radius:5px; margin-bottom:10px;">
    <p><strong>Availability:</strong> ${data.availability}</p>
    <p><strong>Price/Day:</strong> ₹${data.price}</p>
    <p><strong>Rating:</strong> ${data.rating} ⭐</p>
    <p>${data.description}</p>
    <button onclick="editEntry(this)">Edit</button>
    <button onclick="deleteEntry(this)">Delete</button>
  `;
  machineryList.appendChild(card);
}

// ----- VLOG SECTION -----
const vlogForm = document.getElementById("vlogForm");
const vlogList = document.getElementById("vlogList");

vlogForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const inputs = vlogForm.querySelectorAll("input, textarea");

  const vlog = {
    title: inputs[0].value,
    video: inputs[1].value,
    description: inputs[2].value,
  };

  addVlogCard(vlog);
  vlogForm.reset();
});

function addVlogCard(data) {
  const card = document.createElement("div");
  card.classList.add("entry-box");
  card.innerHTML = `
    <h3>${data.title}</h3>
    <iframe src="${data.video}" width="100%" height="200" frameborder="0" allowfullscreen></iframe>
    <p>${data.description}</p>
    <button onclick="editEntry(this)">Edit</button>
    <button onclick="deleteEntry(this)">Delete</button>
  `;
  vlogList.appendChild(card);
}

// ----- EDIT/DELETE FUNCTIONS -----
function deleteEntry(btn) {
  const card = btn.parentElement;
  card.remove();
}

function editEntry(btn) {
  const card = btn.parentElement;
  const name = prompt("Edit Title/Name:", card.querySelector("h3").innerText);
  if (name) {
    card.querySelector("h3").innerText = name;
  }
}
