<?php
// includes/footer.php
?>
  </main>

  <footer
    style="
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: #fff;
      border-top: 1px solid #ccc;
      padding: 1rem;
      box-sizing: border-box;
      z-index: 100;
    "
  >
    <div
      style="
        display: flex;
        align-items: center;    /* keep everything on one horizontal line */
        justify-content: flex-start;
        max-width: 800px;
        margin: 0 auto;
      "
    >
      <!-- Hint -->
      <span
        style="
          font-size: 0.9rem;
          color: #666;
          margin-right: 1rem;
          white-space: nowrap;
        "
      >
        Right-click for more dices!
      </span>

      <!-- Button + dropdown wrapper -->
      <div style="position: relative; margin-right: 2rem;">
        <button id="d20-roll-btn">D20 ROLL</button>

        <!-- Arrow pointing down -->
        <div
          id="dice-arrow-border"
          style="
            display: none;
            position: absolute;
            bottom: -7px;
            right: 10px;
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #ccc;
            z-index: 9;
          "
        ></div>
        <div
          id="dice-arrow"
          style="
            display: none;
            position: absolute;
            bottom: -6px;
            right: 11px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #fff;
            z-index: 10;
          "
        ></div>

        <!-- Dropdown above the button -->
        <div
          id="other-dice-container"
          style="
            display: none;
            position: absolute;
            bottom: 100%;    /* open upwards */
            right: 0;
            background: #fff;
            border: 1px solid #ccc;
            padding: 0.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            z-index: 8;
          "
        >
          <button class="roll-btn" data-sides="4">D4</button><br>
          <button class="roll-btn" data-sides="6">D6</button><br>
          <button class="roll-btn" data-sides="8">D8</button><br>
          <button class="roll-btn" data-sides="10">D10</button><br>
          <button class="roll-btn" data-sides="12">D12</button>
        </div>
      </div>

      <!-- Spacer pushes the result to the far right -->
      <div style="flex: 1;"></div>

      <!-- Roll result -->
      <div
        id="dice-result"
        style="
          font-weight: bold;
          white-space: nowrap;
          margin-left: 1rem;
        "
      ></div>
    </div>

    <script>
      function roll(sides) {
        return Math.floor(Math.random() * sides) + 1;
      }

      const d20Btn = document.getElementById('d20-roll-btn');
      const dropdown = document.getElementById('other-dice-container');
      const arrow = document.getElementById('dice-arrow');
      const arrowBorder = document.getElementById('dice-arrow-border');
      const resultDiv = document.getElementById('dice-result');

      // Left-click rolls D20
      d20Btn.addEventListener('click', () => {
        resultDiv.textContent = 'D20: ' + roll(20);
      });

      // Right-click toggles other dice
      d20Btn.addEventListener('contextmenu', e => {
        e.preventDefault();
        const showing = dropdown.style.display === 'block';
        dropdown.style.display = showing ? 'none' : 'block';
        arrow.style.display = showing ? 'none' : 'block';
        arrowBorder.style.display = showing ? 'none' : 'block';
      });

      // Roll other dice
      document.querySelectorAll('#other-dice-container .roll-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const sides = +btn.dataset.sides;
          resultDiv.textContent = 'D' + sides + ': ' + roll(sides);
        });
      });

      // Click outside closes dropdown
      document.addEventListener('click', e => {
        if (!dropdown.contains(e.target) && e.target !== d20Btn) {
          dropdown.style.display = 'none';
          arrow.style.display = 'none';
          arrowBorder.style.display = 'none';
        }
      });
    </script>
  </footer>
</body>
</html>
