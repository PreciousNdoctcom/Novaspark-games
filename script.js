/* 
 * ENTRANCE PAGE — Animated page transition using DOM manipulation
 * Disables the button and fades the page out before redirecting.
 */

function enterSite() {
 
  const button = document.querySelector('.btn-enter');
  button.disabled = true; // Prevent double-clicks
 
  button.querySelector('span').textContent = 'Loading…'; // Live DOM text update
 
  document.body.style.transition = 'opacity .5s'; // CSS transition via JS
  document.body.style.opacity   = '0';
 
  // Redirect after 500ms so the fade animation has time to complete
  setTimeout(function () {
    window.location.href = 'Home_Page.html';
  }, 500);
 
} 
 
 /* 
 * HOME PAGE — Redirect with user feedback
 * Simple navigation function triggered by the "Discover More" button.
 */
function learnMore() {
  alert("Redirecting to the Games overview page...");
  window.location.href = 'products.php';
}

/* 
 * HOME PAGE News section dynamic content switcher
 * Reads from a JavaScript object (key-value store) and injects HTML into
 * the DOM without a server round-trip, simulating dynamic content.
 */
function showNews(newsId, event) {
  const display = document.getElementById("news-display");

  // News content stored as a JavaScript object — each key maps to an article
  const newsData = {
    n1: {
      date: "MAY 20, 2026",
      headline: "Void Protocol Earns BAFTA Recognition for its Branching Narrative System",
      body: "We are honoured to announce that Void Protocol has received a BAFTA nomination for Best Narrative. The recognition highlights our branching dialogue engine, which allows player choices to reshape the game's world in ways that persist across all three acts. This is a milestone for the studio and for independent game development as a whole."
    },
    n2: {
      date: "MAY 15, 2026",
      headline: "Nebula Drift has a Release Date. Mark your Calendar for 30th June 2026",
      body: "After two years in development, Nebula Drift will officially launch on 30th June 2026 across PC and Mobile platforms simultaneously. The game features over 40 handcrafted race circuits set across asteroid belts, gas giant atmospheres, and collapsing star systems. Pre-registration is now open."
    },
    n3: {
      date: "MAY 10, 2026",
      headline: "Novaspark is Growing. 10 New Developers have Joined the Team",
      body: "Novaspark Games is expanding. Ten new developers have joined the studio, bringing expertise in procedural generation, AI behaviour, and multiplayer systems. The expanded team will work across two unannounced projects alongside continued support for our existing catalogue. We are building something special and we cannot wait to share it."
    },
    n4: {
      date: "MAY 05, 2026",
      headline: "Iron Circuit Open Beta is Live. Sign Up to Play the First Two Tracks",
      body: "The Iron Circuit open beta is now available to download. Players can access the first two race tracks, the full mech customisation suite, and the online leaderboard system. We are actively collecting feedback before the full launch later this year. Head to the Products page to register your interest and secure early access."
    }
  };
 
  // Retrieve the news object matching the clicked button's ID
  const news = newsData[newsId];
  
  // Inject the article content into the display panel using template literals
  display.innerHTML = `
    <div class="news-date">${news.date}</div>
    <div class="news-headline">${news.headline}</div>
    <div class="news-body">${news.body}</div>
  `;
 
  // Toggle the active highlight class across all news buttons
  const buttons = document.querySelectorAll(".news-btn");
  buttons.forEach(btn => btn.classList.remove("news-btn--active"));
  event.currentTarget.classList.add("news-btn--active");
}

/* 
 * HOME PAGE Newsletter form hover effect
 */
function hoverSubmit(element, isHovered) {
  if (isHovered) {
    element.style.filter = "brightness(1.2)";
  } else {
    element.style.filter = "brightness(1.0)";
  }
}
 
/* 
 * HOME PAGE Newsletter signup client-side validation
 * Prevents form submission if required fields are empty and gives the
 * user immediate visual feedback without a page reload.
 */
function handleSignup(event) {
  event.preventDefault(); 
 
  const name = document.getElementById("user-name").value;
  const message = document.getElementById("form-message");
 
  if (name) {
    message.style.color = "var(--success)";
    message.innerText = `Thank you, ${name}! You have been added to our newsletter.`;
  } else {
    message.style.color = "var(--danger)";
    message.innerText = "Please enter your name to subscribe.";
  }
}
 
/* -----------------------------------------------------------------------
 * ORDER PAGE Client-side order form validation
 * Checks that a game has been selected and all fields are filled before
 * allowing the form to POST to process_order.php.
 */

function validateOrderForm(event) {
  const nameInput  = document.getElementById("order-name").value.trim();
  const emailInput = document.getElementById("order-email").value.trim();
  const gameSelect = document.getElementById("order-game").value;
  const feedback   = document.getElementById("order-feedback");
 
  // If any required field is empty, stop form submission and show error
  if (!nameInput || !emailInput || !gameSelect) {
    event.preventDefault();
    feedback.style.color = "var(--danger)";
    feedback.innerText = "✖ Telemetry Exception: All core fields and module allocations must be selected.";
    return false;
  }
 
  // If all fields are valid allow the form to submit to process_order.php
  feedback.style.color = "var(--success)";
  feedback.innerText = "✔ Requisition verified. Dispatching logs over school relays...";
  return true;
}

/* 
 * ABOUT PAGE Team bio modal
 * Retrieves bio data from a JavaScript object and renders it inside a
 * modal overlay without navigating away from the page.
*/

function showBio(id, event) {
  const bios = {
    t1: {
      name: "Precious Nwachukwu",
      role: "Lead Developer & Founder",
      img:  "https://images.unsplash.com/photo-1531123897727-8f129e1688ce?q=80&w=300",
      text: "Precious co-founded Novaspark Games after six years shipping titles at two AAA studios. She leads engine architecture and core gameplay systems, and is the reason the studio's framerate targets are non-negotiable."
    },
    t2: {
      name: "Elena Rostova",
      role: "Art Director",
      img:  "https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=300",
      text: "Elena brings a background in concept art and film production design to every Novaspark title. She sets the visual language of each project from the first mood board to the final colour grade."
    },
    t3: {
      name: "Marcus Thorne",
      role: "Audio Engineer",
      img:  "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=300",
      text: "Marcus composes original scores and builds the full sound design pipeline. His adaptive audio system — which responds in real time to player state — has become one of Novaspark's most praised technical achievements."
    }
  };
 
  const member = bios[id];
  if (!member) return; // Guard: do nothing if the ID doesn't match any bio
 
  // Populate modal fields dynamically using DOM property assignment
  document.getElementById('bio-name').textContent = member.name;
  document.getElementById('bio-role').textContent = member.role;
  document.getElementById('bio-text').textContent = member.text;
  document.getElementById('bio-avatar').innerHTML =
    `<img src="${member.img}" alt="${member.name}"
          style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:2px solid #5c9758;">`;
 
  // Make the modal visible (it is hidden by default via display:none in CSS)
  const modal = document.getElementById('bio-modal');
  modal.style.display = 'flex';
}
 
// Close the bio modal when the X button is clicked
function closeBio() {
  document.getElementById('bio-modal').style.display = 'none';
}

// Also close the modal if the user clicks the dark overlay background
const bioModal = document.getElementById('bio-modal');
if (bioModal) {
  bioModal.addEventListener('click', function(e) {
    if (e.target === this) closeBio(); // Only close if the backdrop itself was clicked
  });
}
 
/* 
 * CONTACT PAGE Client-side contact form validation
 * Validates all required fields before allowing the form to POST to
 * handle_form.php. Mirrors server-side checks performed in handle_form.php.
*/
function validateContactForm(event) {
  event.preventDefault(); // Pause submission until validation passes

  const name     = document.getElementById('contact-name').value.trim();
  const email    = document.getElementById('contact-email').value.trim();
  const age      = document.getElementById('contact-age').value;
  const gender   = document.getElementById('contact-title').value;
  const message  = document.getElementById('contact-comments').value.trim();
  const feedback = document.getElementById('validation-feedback');
 
   // Check every required field, all must have a value
  if (!name || !email || !age || !gender || !message) {
    feedback.textContent = 'Please fill in all required fields.';
    feedback.style.color = 'red';
    return false;
  }
 
  // Validation passed, programmatically submit the form to PHP
  document.getElementById('contact-grid').submit(); // proceed to PHP
  return true;
}
 
function searchCatalog() {
  const searchString = document.getElementById("catalog-search").value.toLowerCase();
  const productCards = document.querySelectorAll(".product-card");
 
  productCards.forEach(card => {
    const gameTitle = card.getAttribute("data-title").toLowerCase();
 
    if (gameTitle.includes(searchString)) {
      card.style.display = "flex";
    } else {
      card.style.display = "none";
    }
  });
}
 
function filterGenre() {
  const selectedGenre = document.getElementById("genre-select").value;
  const productCards = document.querySelectorAll(".product-card");
 
  productCards.forEach(card => {
    const cardGenre = card.getAttribute("data-genre");
 
    if (selectedGenre === "ALL" || cardGenre === selectedGenre) {
      card.style.display = "flex";
    } else {
      card.style.display = "none";
    }
  });
}
 
function purchaseGame(gameName) {
  alert(`Licence Authorization Successful:\n"${gameName}" has been successfully added to your cart matrix queue!`);
}
 
/* SEARCH PAGE Client-side empty search guard
 * Prevents an empty string being submitted to search.php, which would
 * return every record in the database unnecessarily.
 */
function verifySearchInput(event) {
  const queryField  = document.getElementById("search-query").value.trim();
  const errorMessage = document.getElementById("search-error-msg");
 
  if (queryField.length === 0) {
  
    event.preventDefault(); // Block the GET request to search.php
 
    errorMessage.style.color = "#5c9758";
    errorMessage.innerText = "✖ Telemetry Halt: Search queries cannot be processed empty. Please input keywords.";
 
    document.getElementById("search-query").style.borderColor = "#5c9758";
 
    return false;
  }
 
  errorMessage.innerText = "";
  return true; // Allow the form to submit normally
}

 