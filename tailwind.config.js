import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.js',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        brand: {
          50: '#FFFDE6',
          100: '#FFF9B8',
          200: '#FFF58A',
          300: '#FFF15C',
          400: '#FFEE2E',
          500: '#FFEA00',
          600: '#D1C000',
          700: '#A39600',
          800: '#756C00',
          900: '#474100',
          950: '#1A1700',
        },
      },
    },
  },

  plugins: [forms, typography],
}
