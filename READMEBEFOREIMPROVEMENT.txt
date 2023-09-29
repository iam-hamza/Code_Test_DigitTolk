The provided code can be categorized as "OK" code 
It appears to be functional and serves its purpose,

What Makes It "OK" Code:
 # The code appears to perform the intended functionality for managing bookings and related operations.
 #The code maintains some level of consistency 
 #Although minimal, the code does include some error handling, such as checking if certain data is present.

Improvement
 #Validation 
 #SRP (Single Responsibility Principle)
 #Request Class
 #Data Formatting
 #Refactoring
 #Code Consistency
 #Default Values
 #Modularity
 #Separation of Concerns
 #Clean Code
 #Documentation
 #Data Transformation 

 Structure Improvements
 Right now as i dont have any knowladge about the system so i will not be changing the major struture but as i can clearly see the major missing here are as follow
 middlware can be a greate choice rather then checking role in function we can use middlware
 controller is very overloaded seprate the controller in two controllers first resource based controller other for managing coustom methods
 concept of SRP is not used any where will be implementing this
 helpers function should be introduced
 traits should be included
 