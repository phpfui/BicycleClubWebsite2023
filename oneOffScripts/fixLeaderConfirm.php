update ridesignup rs set rs.attended=2 left join rides r on rs.rideId=r.id where rs.attended=0 and
rs.memberId=r.memberId;
