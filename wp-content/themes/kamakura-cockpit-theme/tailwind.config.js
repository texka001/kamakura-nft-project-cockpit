/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./src/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'kmnft-black': '#0a0a12',      // Deep Space Black
        'kmnft-navy': '#1a1f2c',       // Midnight Navy
        'kmnft-green': '#00ff41',      // Neon Green
        'kmnft-gold': '#ffd700',       // Cyber Gold
        'kmnft-white': '#f0f0f0',      // Holographic White tone
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'], // Modern Typography
      },
    },
  },
  plugins: [],
}
