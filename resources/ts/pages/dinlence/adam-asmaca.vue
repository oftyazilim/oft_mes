<template>
  <div class="hangman">
    <h2>Adam Asmaca</h2>
    <div class="hangman-graphic">
      <svg width="120" height="160">
        <!-- Direk -->
        <line x1="20" y1="150" x2="100" y2="150" stroke="#444" stroke-width="4" />
        <line x1="60" y1="150" x2="60" y2="20" stroke="#444" stroke-width="4" />
        <line x1="60" y1="20" x2="90" y2="20" stroke="#444" stroke-width="4" />
        <line x1="90" y1="20" x2="90" y2="40" stroke="#444" stroke-width="4" />
        <!-- Kafa -->
        <circle v-if="wrongCount > 0" cx="90" cy="50" r="10" stroke="#444" stroke-width="3" fill="none" />
        <!-- Gövde -->
        <line v-if="wrongCount > 1" x1="90" y1="60" x2="90" y2="90" stroke="#444" stroke-width="3" />
        <!-- Sol kol -->
        <line v-if="wrongCount > 2" x1="90" y1="70" x2="80" y2="80" stroke="#444" stroke-width="3" />
        <!-- Sağ kol -->
        <line v-if="wrongCount > 3" x1="90" y1="70" x2="100" y2="80" stroke="#444" stroke-width="3" />
        <!-- Sol bacak -->
        <line v-if="wrongCount > 4" x1="90" y1="90" x2="80" y2="110" stroke="#444" stroke-width="3" />
        <!-- Sağ bacak -->
        <line v-if="wrongCount > 5" x1="90" y1="90" x2="100" y2="110" stroke="#444" stroke-width="3" />
      </svg>
    </div>
    <p class="word">
      <span v-for="(char, i) in displayWord" :key="i" class="char">{{ char }}</span>
    </p>
    <div class="input-area">
      <input v-model="inputLetter" maxlength="1" @keyup.enter="guessLetter" placeholder="harf gir" />
      <button @click="guessLetter">Tahmin Et</button>
    </div>
    <p>Kalan Hakkın: <strong>{{ remainingLives }}</strong></p>
    <p>Denediğin Harfler: <span class="letters">{{ guessedLetters.join(', ') }}</span></p>
    <div v-if="gameOver" class="result">
      <p v-if="isWin" class="win">Kazandın! 🎉</p>
      <p v-else class="lose">Kaybettin! Doğru kelime: <strong>{{ word }}</strong></p>
      <button @click="resetGame">Tekrar Oyna</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { hasAccess, canAccess } from '@layouts/plugins/casl'

// definePage({
//   meta: {
//     action: "read",
//     subject: "dashboard",
//   },
// });

const words = ['vue', 'javascript', 'hangman', 'bilgisayar', 'programlama']
const word = ref(words[Math.floor(Math.random() * words.length)])
const guessedLetters = ref<string[]>([])
const inputLetter = ref('')
const maxLives = 6
const wrongCount = computed(() =>
  guessedLetters.value.filter(l => !word.value.includes(l)).length
)
const remainingLives = computed(() => maxLives - wrongCount.value)

const displayWord = computed(() =>
  word.value
    .split('')
    .map(char => (guessedLetters.value.includes(char) ? char : '_'))
)

const isWin = computed(() => displayWord.value.join('') === word.value)
const isLose = computed(() => remainingLives.value <= 0)
const gameOver = computed(() => isWin.value || isLose.value)

function guessLetter() {
  const letter = inputLetter.value.toLowerCase()
  if (!letter || guessedLetters.value.includes(letter) || gameOver.value) return
  guessedLetters.value.push(letter)
  inputLetter.value = ''
}

function resetGame() {
  word.value = words[Math.floor(Math.random() * words.length)]
  guessedLetters.value = []
  inputLetter.value = ''
}
</script>

<style scoped>
.hangman {
  padding: 2rem;
  border: 1px solid #eee;
  border-radius: 12px;
  background: #fafbfc;
  box-shadow: 0 2px 8px #0001;
  margin-block: 2rem;
  margin-inline: auto;
  max-inline-size: 400px;
  text-align: center;
}

.hangman-graphic {
  margin-block-end: 1rem;
}

.word {
  font-size: 2rem;
  letter-spacing: 0.5rem;
  margin-block: 1rem;
  margin-inline: 0;
}

.char {
  display: inline-block;
  border-block-end: 2px solid #bbb;
  inline-size: 1.5rem;
  margin-block: 0;
  margin-inline: 0.2rem;
  text-transform: uppercase;
}

.input-area {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-block-end: 1rem;
}

input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  inline-size: 80px;
  text-align: center;
}

button {
  border: none;
  border-radius: 6px;
  background: #1976d2;
  color: #fff;
  cursor: pointer;
  font-weight: bold;
  padding-block: 0.5rem;
  padding-inline: 1rem;
  transition: background 0.2s;
}

button:hover {
  background: #1565c0;
}

.letters {
  color: #1976d2;
  font-weight: bold;
}

.result {
  margin-block-start: 1rem;
}

.win {
  color: #43a047;
  font-weight: bold;
}

.lose {
  color: #e53935;
  font-weight: bold;
}
</style>
