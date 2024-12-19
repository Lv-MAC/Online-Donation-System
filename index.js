// Get the total credit and username from the PHP server
const totalCreditElement = document.getElementById('total-credits');
const usernameElement = document.getElementById('username');

let totalCredit = parseFloat(totalCreditElement.textContent);
let username = usernameElement.textContent;

// Function to display user credits
function displayCredits() {
  document.getElementById('total-credits').textContent = totalCredit.toFixed(2);
}

// Donation function
function donate(eventName) {
  const inputField = document.getElementById(`Amount-field-${eventName.toLowerCase()}`);
  const donationAmount = parseFloat(inputField.value);

  if (!donationAmount || donationAmount <= 0) {
    alert('Please enter a valid donation amount.');
    return;
  }

  if (donationAmount > totalCredit) {
    alert('Insufficient credits!');
    return;
  }

  // Deduct the donation amount from user credits
  totalCredit -= donationAmount;
  displayCredits();

  // Show success message
  alert(`You have successfully donated ${donationAmount} BDT for the ${eventName} cause.`);

  // Clear input field
  inputField.value = '';
}

const donationButtons = document.querySelectorAll(".donate-btn");

donationButtons.forEach(button => {
  button.addEventListener("click", function () {
    const donationCard = button.closest(".donation-card");
    const amountInput = donationCard.querySelector(".amount-input");
    const eventName = donationCard.querySelector(".event_name").innerText;
    donate(eventName);
  });
});
