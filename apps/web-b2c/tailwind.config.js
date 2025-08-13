/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        setrag: {
          primary: '#0B5AA2',
          secondary: '#00A859',
          'primary-light': '#4A90E2',
          'primary-dark': '#003D82',
          'secondary-light': '#4CAF50',
          'secondary-dark': '#2E7D32',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
