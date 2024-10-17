/* the symptom is that search breaks and content browser just spins with 500 error */
/* This is a known problem from upgrades */
delete from ezcontentobject_link where relation_type = 0;

