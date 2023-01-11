module.exports = {
  content: [
      './templates/**/*.hbs',
			'./layouts/**/*.hbs',
      './layouts/**/*.ts',
			'./src/ts/**/*.ts'
  ],
  theme: {
    screens: {
      'xm': {'max': '580px'},
      'sm': {'min': '581px', 'max': '767px'},
      'md': {'min': '768px', 'max': '1279px'},
      'lg': {'min': '1280px', 'max': '1560px'},
      'xl': {'min': '1561px'},
    },
    extend: {
      margin: {
        "12":"5rem",
        "13":"6rem",
        "14":"7rem",
        "15":"8rem",
        "18":"10rem"
      },
      lineHeight: {
        '12': '2.75rem',
      }
    }
  },
plugins: [],
}





