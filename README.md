FacBoook Clone API
- Features
    1. User Resistration(Login, Register , delete, Account)
    2. Post Creation(create , update, delete)
    3. Like Post(like Unlike)
    4. Comment on Post (Create update Delete)

- Additional Features
    1. User Profile Image Upload
    2. Post Image Uplaod
    3. Post Pagination
    4. Post Search

## users
    - email 
    - password
    - name
    - profile_image
    - created_at
    - update_at

## post
    - user_id int not null
    - caption text not nul
    - image varchar
    - created_at
    - update_at

## like
    - user_id int not null
    - post_id 
    - created_at timestamp
    - update_at timestamp not nul

## comments
    - user_id int not nul
    - post_id int not null
    - comment text not null
    - created_at timestamp not null
    - update_at timestamp not null

## api ention

