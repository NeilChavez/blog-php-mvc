
#  Design Database for a Blog
---
## Entities
---
### users - **data entity** 
* user_id **PK**
* username 
* avatar
* email 
* password 
* role
* token 
* created_at
* updated_at 

### posts - **data entity** 
* post_id **PK**
* title 
* slug
* featured_image
* content 
* status 
* created_at
* updated_at 
* user_id **FK**
* category **FK**

### comments 
* comment_id **PK**
* content
* status 
* created_at
* updated_at 
* user_id **FK**
* post_id **FK**

## category 
* category_id **PK**
* name **UQ**
* description

## category_x_posts
* category_id 
* post_id 
* (category_id, post_id) **FK**

---
## Relations 
---
* Every **user** has many **posts** (1-N)
* Every **post** has many **comments** (1-N)
* Every **user** has many **comments** (1-N)
* Many **posts** has many **categories** and viceversa (M-M). That's why a pivot table category_x_posts is used.

---
## Business Rules
---
## users 
 * Create a user
 * Read all users 
 * Read a user 
 * Update user data
 * Delete user 

## posts
* Create a post
* update post
* Read a single post
* Delete a post
* Read all posts 
* Read all posts by a user

## categories
* Create category
* Update category
* Read a single category
* Read all categories
* Delete a category

## comments 
* Create a comment
* Read all comments by post
* Read a comment
* Delete a comment 

## category_x_posts
* Create a relationship
* Read all posts of a category
* Read all categories of a post
* Delete a category_x_posts;


 








