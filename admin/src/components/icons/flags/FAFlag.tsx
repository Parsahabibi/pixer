import Image from "next/image";

export const FaFlag = ({ width = '640px', height = '480px' }) => {
    return (
        <svg style={{objectFit:'cover'}} width="30" height="30" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="30" height="10" fill="#00A859" />
            <rect x="0" y="100" width="30" height="10" fill="#FFFFFF" />
            <rect x="0" y="200" width="30" height="10" fill="#E70D2E" />
        </svg>
    );
};
