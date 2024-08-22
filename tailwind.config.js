/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/App/Views/**/*.php",
    "./src/App/Views/**/*.html",
    "node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
