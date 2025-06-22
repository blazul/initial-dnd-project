<?php
// includes/footer.php
?>
  </main>

  <footer style="padding:1rem; border-top:1px solid #ccc;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        &copy; <?= date('Y') ?> D&amp;D Character Manager
      </div>
      <div style="position:relative;">
        <button id="d20-roll-btn">D20 ROLL</button>

        <!-- Hidden by default -->
        <div id="other-dice-container" style="display:none; position:absolute; right:0; top:2rem; background:#fff; border:1px solid #ccc; padding:0.5rem;">
          <button class="roll-btn" data-sides="4">D4</button><br>
          <button class="roll-btn" data-sides="6">D6</button><br>
          <button class="roll-btn" data-sides="8">D8</button><br>
          <button class="roll-btn" data-sides="10">D10</button><br>
          <button class="roll-btn" data-sides="12">D12</button>
        </div>

        <div id="dice-result" style="margin-top:0.5rem;font-weight:bold;"></div>
      </div>
    </div>

    <script>
      function roll(sides) {
        return Math.floor(Math.random() * sides) + 1;
      }

      const d20Btn = document.getElementById('d20-roll-btn');
      const dropdown = document.getElementById('other-dice-container');
      const resultDiv = document.getElementById('dice-result');

      // Left-click rolls D20
      d20Btn.addEventListener('click', () => {
        resultDiv.textContent = 'D20: ' + roll(20);
      });

      // Right-click toggles other dice
      d20Btn.addEventListener('contextmenu', e => {
        e.preventDefault();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
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
        }
      });
    </script>
  </footer>
</body>
</html>
