describe('Register form', () => {
    it('Test 1 - Register - OK', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8313/register')

        cy.get('#registration_form_email').type('test2@gmail.com')
        cy.get('#registration_form_plainPassword').type('test2test2')
        cy.get('#registration_form_agreeTerms').click()

        cy.get('button[type="submit"]').click()

        cy.contains('Login').should('exist')
    })

    it('Test 2 - Register - KO', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8313/register')

        cy.get('#registration_form_email').type('test2')
        cy.get('#registration_form_plainPassword').type('test2')
        cy.get('#registration_form_agreeTerms').click()

        cy.get('button[type="submit"]').click()

        cy.contains('Your password should be at least 8 characters').should('exist')
    })
})