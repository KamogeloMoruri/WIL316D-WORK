// ---- Load and render donations on page load ----

function formatDate(isoString) {
    const d = new Date(isoString.replace(" ", "T") + "Z");
    if (isNaN(d)) return isoString;
    return d.toLocaleDateString("en-ZA");
}

function loadDonations() {
    fetch("get_donations.php")
        .then(response => response.json())
        .then(data => {
            document.getElementById("totalDonations").textContent =
                "R" + Number(data.total).toFixed(2);

            const tbody = document.getElementById("donationsTable");
            tbody.innerHTML = "";

            if (!data.donations || data.donations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">No donations yet.</td></tr>';
                return;
            }

            data.donations.forEach(donation => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${donation.donor_name}</td>
                    <td>R${Number(donation.amount).toFixed(2)}</td>
                    <td>${donation.purpose}</td>
                    <td>${formatDate(donation.created_at)}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(() => {
            // Backend not reachable yet (e.g. PHP not running) - fail quietly,
            // the page still works, it just won't show live data.
        });
}

document.addEventListener("DOMContentLoaded", loadDonations);

// ---- Add a donation ----

let form = document.getElementById("donationForm");

form.addEventListener("submit", function (event) {
    event.preventDefault();

    let name = document.getElementById("donorName").value.trim();
    let amount = document.getElementById("amount").value;
    let purpose = document.getElementById("purpose").value;

    if (name === "" || amount === "" || purpose === "") {
        alert("Please fill in all fields.");
        return;
    }

    let data = new FormData();
    data.append("donorName", name);
    data.append("amount", amount);
    data.append("purpose", purpose);

    fetch("save_donation.php", {
        method: "POST",
        body: data
    })
        .then(response => response.text())
        .then(result => {
            alert(result);
            form.reset();
            loadDonations();
        })
        .catch(() => {
            alert("Error connecting to the server. Is the PHP backend running?");
        });
});

// ---- AI Donation Assistant ----

document.getElementById("askAiBtn").addEventListener("click", function () {
    const question = document.getElementById("question").value.trim();
    const responseEl = document.getElementById("aiResponse");

    if (question === "") {
        alert("Please type a question first.");
        return;
    }

    responseEl.textContent = "Thinking...";

    fetch("ai_assistant.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ question })
    })
        .then(response => response.json())
        .then(result => {
            responseEl.textContent = result.answer;
        })
        .catch(() => {
            responseEl.textContent = "Error connecting to the server. Is the PHP backend running?";
        });
});

// ---- Cardano transaction verification ----

document.getElementById("verifyTxBtn").addEventListener("click", function () {
    const transactionId = document.getElementById("transactionId").value.trim();
    const resultEl = document.getElementById("cardanoResult");

    if (transactionId === "") {
        alert("Please enter a transaction ID.");
        return;
    }

    resultEl.textContent = "Checking...";

    fetch("verify_transaction.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ transactionId })
    })
        .then(response => response.json())
        .then(result => {
            resultEl.textContent = result.message;
        })
        .catch(() => {
            resultEl.textContent = "Error connecting to the server. Is the PHP backend running?";
        });
});
