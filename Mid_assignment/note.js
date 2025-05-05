const active1 = document.getElementsByClassName("active")

for (let i = 0; i < active1.length; i++) {
  active1[i].addEventListener("mouseover", () => {
    active1[i].style.backgroundColor = "rgb(212, 127, 127)";
    active1[i].style.transition = "0.5s"
  });

  active1[i].addEventListener("mouseout", () => {
    active1[i].style.backgroundColor = ""
    active1[i].style.transition = "0.5s"
  });
}


document.addEventListener("DOMContentLoaded", function () {
  const check = document.getElementById("check")
  const moto = document.getElementById("moto")

  check.addEventListener("change", function () {
    if (check.checked) {
      moto.style.display = "none"
    }
    
    else {
      moto.style.display = "block"
    }
  });

  const taglines = document.querySelectorAll('.tagline')
let index = 0;

function showTagline() {
  taglines.forEach(line => {
    line.style.display = 'none'
    line.classList.remove('fadeIn')
  });

  const current = taglines[index]
  current.style.display = 'block'
  current.classList.add('fadeIn')
  index = (index + 1) % taglines.length


  setTimeout(showTagline, 1000)
}

setTimeout(showTagline, 1000); 

});




//for register

const emp_id = document.getElementById("emp_id")
const name = document.getElementById("name")
const email = document.getElementById("email")
const gender = document.getElementsByName("gender")
const dept = document.getElementById("dept")
const date = document.getElementById("date")
const submit_btn = document.getElementById("submitbtn")

submit_btn.addEventListener("click", (event) => {
  event.preventDefault()

  let isValid = true

  const emp_id_value = emp_id.value.trim()
  const emp_regex = /^EMP\d{3}$/
  const emp_id_req = document.getElementById("emp_id_req")
  if (!emp_regex.test(emp_id_value)) {
    emp_id_req.style.display = "block"
    emp_id_req.innerHTML = "Employee ID must start with 'EMP' followed by 3 digits"
    isValid = false
  }

  const name_value = name.value.trim()
  const name_regex = /^[A-Za-z\s]+$/
  const name_req = document.getElementById("name_req")
  if (!name_regex.test(name_value)) {
    name_req.style.display = "block"
    name_req.innerHTML = "Name must only contain alphabets and spaces"
    isValid = false
  }

  const email_value = email.value.trim()
  const email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/
  const email_req = document.getElementById("email_req")
  if (!email_regex.test(email_value)) {
    email_req.style.display = "block"
    email_req.innerHTML = "Please enter a valid email address"
    isValid = false
  }

  const dept_value = dept.value
  const dept_req = document.getElementById("dept_req")
  if (dept_value === "") {
    dept_req.style.display = "block"
    dept_req.innerHTML = "Please select a department"
    isValid = false
  }

  const date_value = date.value.trim();
  const date_req = document.getElementById("date_req")
  if (!date_value) {
    date_req.style.display = "block"
    date_req.innerHTML = "Joining Date must not be empty"
    isValid = false
  }

  const gender_req = document.getElementById("gender_req")
  if (!(document.getElementById("male").checked || document.getElementById("female").checked)) {
    gender_req.style.display = "block"
    gender_req.innerHTML = "Please select your gender"
    isValid = false
  }

  if (isValid) {
    alert("Form submitted successfully!");
    document.getElementById("emp_id_req").style.display = "none"
  document.getElementById("name_req").style.display = "none"
  document.getElementById("email_req").style.display = "none"
  document.getElementById("gender_req").style.display = "none"
  document.getElementById("dept_req").style.display = "none"
  document.getElementById("date_req").style.display = "none"
  


let selectedGender
if (document.getElementById("male").checked) {
  selectedGender = "Male"
} else if (document.getElementById("female").checked) {
  selectedGender = "Female"
}


let empType = document.querySelector('input[type="checkbox"]').checked ? "Fulltime" : "Part-time"


document.getElementById("display_ID").innerHTML = emp_id_value
document.getElementById("display_name").innerHTML = name_value
document.getElementById("display_email").innerHTML = email_value
document.getElementById("display_Gender").innerHTML = selectedGender
document.getElementById("display_dept").innerHTML = dept_value
document.getElementById("display_jDate").innerHTML = date_value
document.getElementById("display_empType").innerHTML = empType




document.getElementById("formcontainer2").style.display = "block"

    
  }
});

const resetbtn=document.getElementById("resetbtn")
 resetbtn.addEventListener("click",()=>
 {
  document.getElementById("emp_id_req").style.display = "none"
  document.getElementById("name_req").style.display = "none"
  document.getElementById("email_req").style.display = "none"
  document.getElementById("gender_req").style.display = "none"
  document.getElementById("dept_req").style.display = "none"
  document.getElementById("date_req").style.display = "none"

  document.getElementById("formcontainer2").style.display="none"


 }

)

//Employee list

