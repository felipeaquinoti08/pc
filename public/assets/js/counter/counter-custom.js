// Comma/Separator counter
(function () {
  const counters = document.querySelectorAll(".counter");
  const speed = 70;

  counters.forEach((counter) => {
    const updateCount = () => {
      const target = parseInt(counter.getAttribute("data-target"));
      const count = parseInt(counter.innerText.replace(/,/g, "")); // Remove commas from displayed count

      // Math.trunc(target / speed) rounds down to 0 for any target under
      // ~70 (e.g. a customer count of "4"), which stalls the counter at 0
      // forever since count + 0 never reaches target. Real dashboard
      // numbers are often small, so always step by at least 1.
      const increment = Math.max(1, Math.trunc(target / speed));

      if (count < target) {
        let updatedCount = count + increment;

        // Format the number with commas for lakh and crore
        updatedCount = updatedCount.toLocaleString("en-IN");

        counter.innerText = updatedCount;
        setTimeout(updateCount, 1);
      } else {
        // Update counter.innerText with formatted target value
        counter.innerText = target.toLocaleString("en-IN");
      }
    };

    updateCount();
  });
})();
