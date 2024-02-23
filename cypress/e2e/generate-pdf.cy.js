describe('Generate pdf form', () => {
    it('Test 2 - Generate pdf - KO', () => {
        // login to access generate-pdf
        cy.visit('http://symfony.mmi-troyes.fr:8313/login')

        cy.get('#username').type('test@gmail.com')
        cy.get('#password').type('test')

        cy.get('button[type="submit"]').click()

        // generate-pdf
        cy.visit('http://symfony.mmi-troyes.fr:8313/pdf/generate')

        cy.get('#pdf_form_url').type('fake')

        cy.get('#pdf_form_pdfName').type('test')

        cy.get('button[type="submit"]').click()

        cy.contains('Le fichier PDF n\'a pas pu être généré.').should('exist')
    })

    it('Test 1 - Generate pdf - OK', () => {
        // login to access generate-pdf
        cy.visit('http://symfony.mmi-troyes.fr:8313/login')

        cy.get('#username').type('test@gmail.com')
        cy.get('#password').type('test')

        cy.get('button[type="submit"]').click()

        // generate-pdf
        cy.visit('http://symfony.mmi-troyes.fr:8313/pdf/generate')

        cy.get('#pdf_form_url').type('https://www.cypress.io/')

        cy.get('#pdf_form_pdfName').type('test')

        cy.get('button[type="submit"]').click()
    })
})