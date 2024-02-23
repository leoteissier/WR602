describe('Login form', () => {
    it('Test 1 - Login - OK', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8313/login')

        cy.get('#username').type('test@gmail.com')
        cy.get('#password').type('test')

        cy.get('button[type="submit"]').click()

        cy.contains('Bienvenue sur notre service de génération de PDF').should('exist')
    })

    it('Test 2 - Login - KO', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8313/login')

        cy.get('#username').type('devFake@gmail.com')
        cy.get('#password').type('devFake')

        cy.get('button[type="submit"]').click()

        cy.contains('Invalid credentials.').should('exist')
    })
})