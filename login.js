function login() {
    let usernameDOM = document.querySelector('input[name=username]')
    let passwordDOM =document.querySelector('input[name=password]')

    let data = {
        username : usernameDOM.value ,
        passwordDOM : passwordDOM.value
    }
    console.log(data) 
  
}   