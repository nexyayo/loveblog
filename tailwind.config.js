/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./includes/**/*.{php,html,js}",
    "./pages/**/*.{php,html,js}",
    "./components/**/*.{php,html,js}",
    "./*.{php,html,js}"
  ],
  theme: {
    extend: {
      colors: {
        'primary': '#E34B76', // Kolor przewodni strony
        'primary-dark': '#d33863',
      },
      fontFamily: {
        'sen': ['Sen', 'sans-serif'],
      },
      spacing: {
        '40px': '40px',
        '50px': '50px',
        '60px': '60px',
        '90px': '90px',
        '105px': '105px',
        '215px': '215px',
        '260px': '260px',
      },
      backgroundImage: {
        'button-gradient': 'linear-gradient(90deg, #F91A1A 0%, #B7687F 42%, #E34B76 100%)',
      },
    },
  },
  plugins: [],
}